<?php

namespace SeQura\Core\BusinessLogic\Domain\OrderCleanup\Services;

use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\OrderRequestStates;
use SeQura\Core\BusinessLogic\Domain\Order\Models\SeQuraOrder;
use SeQura\Core\BusinessLogic\Domain\Order\RepositoryContracts\SeQuraOrderRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\OrderCleanup\RepositoryContracts\OrderCleanupScheduleRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Time\ClockInterface;

/**
 * Class OrderCleanupService
 *
 * Removes the solicitations that never became a shop order and schedules the daily run that does it.
 *
 * @package SeQura\Core\BusinessLogic\Domain\OrderCleanup\Services
 */
class OrderCleanupService
{
    /**
     * How many solicitations are read and deleted at once.
     */
    protected const BATCH_SIZE = 500;

    /**
     * How many days a solicitation is kept after it was last solicited.
     */
    protected const RETENTION_DAYS = 7;

    /**
     * @var SeQuraOrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var OrderCleanupScheduleRepositoryInterface
     */
    protected $scheduleRepository;

    /**
     * @var ClockInterface
     */
    protected $clock;

    /**
     * @param SeQuraOrderRepositoryInterface $orderRepository
     * @param OrderCleanupScheduleRepositoryInterface $scheduleRepository
     * @param ClockInterface $clock
     */
    public function __construct(
        SeQuraOrderRepositoryInterface $orderRepository,
        OrderCleanupScheduleRepositoryInterface $scheduleRepository,
        ClockInterface $clock
    ) {
        $this->orderRepository = $orderRepository;
        $this->scheduleRepository = $scheduleRepository;
        $this->clock = $clock;
    }

    /**
     * Deletes the solicitations without a shop order that were last solicited RETENTION_DAYS days ago or earlier.
     *
     * A solicitation SeQura already settled is kept, and so is one whose solicitation time is unknown: an order
     * stored before that time was kept falls back to its cart's update time, and is kept when that is missing
     * or unreadable too, so nothing is deleted on a guess. Nothing is read or deleted when the order storage cannot
     * delete by condition.
     *
     * @param callable|null $afterEachBatch Called after every batch, e.g. to report that the task is alive.
     *
     * @return int Number of stale solicitations sent for deletion. One linked to a shop order since it was read is
     * kept, but still counted.
     */
    public function cleanup(?callable $afterEachBatch = null): int
    {
        if (!$this->canCleanup()) {
            return 0;
        }

        $cutoff = $this->clock->now()->modify('-' . static::RETENTION_DAYS . ' days')->getTimestamp();
        $deleted = 0;
        $lastId = 0;

        do {
            $orders = $this->orderRepository->getUnlinkedOrdersAfter($lastId, static::BATCH_SIZE);
            if (empty($orders)) {
                break;
            }

            $lastId = (int)\end($orders)->getId();
            $staleIds = [];
            foreach ($orders as $order) {
                if ($this->isStale($order, $cutoff)) {
                    $staleIds[] = (int)$order->getId();
                }
            }

            $this->orderRepository->deleteUnlinkedOrders($staleIds);
            $deleted += \count($staleIds);
            if ($afterEachBatch !== null) {
                $afterEachBatch();
            }
        } while (\count($orders) === static::BATCH_SIZE);

        return $deleted;
    }

    /**
     * Whether the order storage can delete stale solicitations safely, i.e. on the condition that they are still
     * not linked to a shop order.
     *
     * @return bool
     */
    public function canCleanup(): bool
    {
        return $this->orderRepository->canDeleteUnlinkedOrders();
    }

    /**
     * Schedules the first cleanup run, keeping an existing schedule unless told to overwrite it.
     *
     * @param int $firstRunTimestamp
     * @param bool $overwrite
     *
     * @return void
     */
    public function schedule(int $firstRunTimestamp, bool $overwrite = false): void
    {
        if (!$overwrite && $this->getNextRun() !== null) {
            return;
        }

        $this->scheduleRepository->setNextRun($firstRunTimestamp);
    }

    /**
     * Returns the timestamp of the next cleanup run, or null when the cleanup was never scheduled.
     *
     * @return int|null
     */
    public function getNextRun(): ?int
    {
        return $this->scheduleRepository->getNextRun();
    }

    /**
     * Whether the cleanup is scheduled and its next run time has come.
     *
     * @return bool
     */
    public function isDue(): bool
    {
        $nextRun = $this->getNextRun();

        return $nextRun !== null && $nextRun <= $this->clock->now()->getTimestamp();
    }

    /**
     * Moves the next cleanup run a day ahead, as many times as it takes to get past now, so ticks that came late
     * or a task runner that was down do not shift the scheduled time of day. Days are counted in the timezone of
     * the time the clock returns, PHP's default timezone unless the host sets another.
     *
     * @return void
     */
    public function advanceNextRun(): void
    {
        $now = $this->clock->now();
        $nextRun = (clone $now)->setTimestamp($this->getNextRun() ?? $now->getTimestamp());
        while ($nextRun <= $now) {
            $nextRun->modify('+1 day');
        }

        $this->scheduleRepository->setNextRun($nextRun->getTimestamp());
    }

    /**
     * Whether the solicitation is still unsettled and was last solicited at or before the cutoff.
     *
     * @param SeQuraOrder $order
     * @param int $cutoff
     *
     * @return bool
     */
    protected function isStale(SeQuraOrder $order, int $cutoff): bool
    {
        if (\in_array($order->getState(), OrderRequestStates::SETTLED_STATES, true)) {
            return false;
        }

        $solicitedAt = $this->getSolicitationTime($order);

        return $solicitedAt !== null && $solicitedAt <= $cutoff;
    }

    /**
     * Returns when the order was last solicited, falling back to its cart's update time for an order stored
     * before the solicitation time was kept, or null when neither is known.
     *
     * @param SeQuraOrder $order
     *
     * @return int|null
     */
    protected function getSolicitationTime(SeQuraOrder $order): ?int
    {
        if ($order->getSolicitedAt() !== null) {
            return $order->getSolicitedAt();
        }

        $updatedAt = $order->getUnshippedCart()->getUpdatedAt();
        if ($updatedAt === null || $updatedAt === '') {
            return null;
        }

        $updatedAtTimestamp = strtotime($updatedAt);

        return $updatedAtTimestamp === false ? null : $updatedAtTimestamp;
    }
}
