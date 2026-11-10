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
     * Seconds between two cleanup runs.
     */
    protected const RUN_INTERVAL = 86400;

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
     * @return int|null Number of stale solicitations sent for deletion, or null when the cleanup was skipped. One
     * linked to a shop order since it was read is kept, but still counted.
     */
    public function cleanup(?callable $afterEachBatch = null): ?int
    {
        if (!$this->orderRepository->canDeleteUnlinkedOrders()) {
            return null;
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
     * When the cleanup is scheduled and its next run time has come, moves the next run ahead by whole RUN_INTERVALs
     * until it is past now and returns the run that was due. Ticks that came late or a task runner that was down
     * do not shift the run time. Runs are a fixed number of seconds apart, so the local time of day moves by an
     * hour across a daylight saving change, but never drifts.
     *
     * @return int|null The run that was due, or null when no run is due.
     */
    public function claimDueRun(): ?int
    {
        $now = $this->clock->now()->getTimestamp();
        $dueRun = $this->getNextRun();
        if ($dueRun === null || $dueRun > $now) {
            return null;
        }

        $this->scheduleRepository->setNextRun(
            $dueRun + (\intdiv($now - $dueRun, static::RUN_INTERVAL) + 1) * static::RUN_INTERVAL
        );

        return $dueRun;
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
        $solicitedAt = $order->getSolicitedAt();
        if ($solicitedAt !== null) {
            return $solicitedAt;
        }

        $updatedAt = $order->getUnshippedCart()->getUpdatedAt();
        $updatedAtTimestamp = $updatedAt === null ? false : strtotime($updatedAt);

        return $updatedAtTimestamp === false ? null : $updatedAtTimestamp;
    }
}
