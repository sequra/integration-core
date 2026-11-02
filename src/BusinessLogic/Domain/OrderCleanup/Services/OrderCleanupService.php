<?php

namespace SeQura\Core\BusinessLogic\Domain\OrderCleanup\Services;

use SeQura\Core\BusinessLogic\Domain\Order\Models\SeQuraOrder;
use SeQura\Core\Infrastructure\Configuration\ConfigurationManager;
use SeQura\Core\Infrastructure\Logger\LogContextData;
use SeQura\Core\Infrastructure\Logger\Logger;
use SeQura\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use SeQura\Core\Infrastructure\ORM\Interfaces\ConditionallyDeletes;
use SeQura\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use SeQura\Core\Infrastructure\ORM\QueryFilter\Operators;
use SeQura\Core\Infrastructure\ORM\QueryFilter\QueryFilter;
use SeQura\Core\Infrastructure\Utility\TimeProvider;

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
     * Name of the global configuration value holding the timestamp of the next cleanup run.
     */
    public const NEXT_RUN_CONFIG_KEY = 'orderCleanupNextRun';

    /**
     * How many solicitations are read and deleted at once.
     */
    protected const BATCH_SIZE = 500;

    /**
     * How many days a solicitation is kept after its cart was last updated.
     */
    protected const RETENTION_DAYS = 7;

    /**
     * Seconds between two cleanup runs.
     */
    protected const RUN_INTERVAL = 86400;

    /**
     * @var RepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var TimeProvider
     */
    protected $timeProvider;

    /**
     * @var ConfigurationManager
     */
    protected $configurationManager;

    /**
     * @param RepositoryInterface $orderRepository Repository of the SeQuraOrder entity.
     * @param TimeProvider $timeProvider
     * @param ConfigurationManager $configurationManager
     */
    public function __construct(
        RepositoryInterface $orderRepository,
        TimeProvider $timeProvider,
        ConfigurationManager $configurationManager
    ) {
        $this->orderRepository = $orderRepository;
        $this->timeProvider = $timeProvider;
        $this->configurationManager = $configurationManager;
    }

    /**
     * Deletes the solicitations without a shop order whose cart was last updated RETENTION_DAYS days ago or
     * earlier, or carries no update time at all.
     *
     * The solicitations without a shop order reference are read in id order, once for an empty and once for
     * a missing reference, so every row is read once whatever is deleted along the way.
     *
     * @param callable|null $afterEachBatch Called after every batch, e.g. to report that the task is alive.
     *
     * @return int Number of deleted solicitations.
     *
     * @throws QueryFilterInvalidParamException
     */
    public function cleanup(?callable $afterEachBatch = null): int
    {
        $cutoff = $this->timeProvider->getCurrentLocalTime()
            ->modify('-' . static::RETENTION_DAYS . ' days')
            ->getTimestamp();
        $deleted = 0;

        foreach ([Operators::EQUALS, Operators::NULL] as $orderRef1Operator) {
            $lastId = 0;
            do {
                /**
                 * @var SeQuraOrder[] $orders
                 */
                $orders = $this->orderRepository->select($this->getSolicitationsFilter($orderRef1Operator, $lastId));
                if (empty($orders)) {
                    break;
                }

                $lastId = (int)\end($orders)->getId();
                $staleOrders = \array_values(\array_filter($orders, function (SeQuraOrder $order) use ($cutoff) {
                    return $this->isStale($order, $cutoff);
                }));

                $this->deleteOrders($staleOrders, $orderRef1Operator);
                $deleted += \count($staleOrders);
                if ($afterEachBatch !== null) {
                    $afterEachBatch();
                }
            } while (\count($orders) === static::BATCH_SIZE);
        }

        Logger::logInfo("Order cleanup deleted $deleted stale solicitations.");

        return $deleted;
    }

    /**
     * Schedules the first cleanup run, keeping an existing schedule unless told to overwrite it.
     *
     * @param int $firstRunTimestamp
     * @param bool $overwrite
     *
     * @return void
     *
     * @throws QueryFilterInvalidParamException
     */
    public function schedule(int $firstRunTimestamp, bool $overwrite = false): void
    {
        if (!$overwrite && $this->getNextRun() !== null) {
            return;
        }

        $this->configurationManager->saveConfigValue(static::NEXT_RUN_CONFIG_KEY, $firstRunTimestamp, false);
    }

    /**
     * Returns the timestamp of the next cleanup run, or null when the cleanup was never scheduled.
     *
     * @return int|null
     *
     * @throws QueryFilterInvalidParamException
     */
    public function getNextRun(): ?int
    {
        $nextRun = $this->configurationManager->getConfigValue(static::NEXT_RUN_CONFIG_KEY, null, false);

        return is_numeric($nextRun) ? (int)$nextRun : null;
    }

    /**
     * Whether the cleanup is scheduled and its next run time has come.
     *
     * @return bool
     *
     * @throws QueryFilterInvalidParamException
     */
    public function isDue(): bool
    {
        $nextRun = $this->getNextRun();

        return $nextRun !== null && $nextRun <= $this->timeProvider->getCurrentLocalTime()->getTimestamp();
    }

    /**
     * Moves the next cleanup run to the first scheduled time of day that is still ahead of now, so the run keeps
     * the time the host scheduled even after ticks came late or the task runner was down for a while.
     *
     * @return void
     *
     * @throws QueryFilterInvalidParamException
     */
    public function advanceNextRun(): void
    {
        $now = $this->timeProvider->getCurrentLocalTime()->getTimestamp();
        $nextRun = $this->getNextRun() ?? $now;
        while ($nextRun <= $now) {
            $nextRun += static::RUN_INTERVAL;
        }

        $this->configurationManager->saveConfigValue(static::NEXT_RUN_CONFIG_KEY, $nextRun, false);
    }

    /**
     * Returns the filter selecting the next batch of solicitations with an empty (EQUALS) or a missing (NULL)
     * shop order reference after the given id.
     *
     * @param string $orderRef1Operator Operators::EQUALS or Operators::NULL.
     * @param int $afterId
     *
     * @return QueryFilter
     *
     * @throws QueryFilterInvalidParamException
     */
    protected function getSolicitationsFilter(string $orderRef1Operator, int $afterId): QueryFilter
    {
        return (new QueryFilter())
            ->where('orderRef1', $orderRef1Operator, $orderRef1Operator === Operators::EQUALS ? '' : null)
            ->where('id', Operators::GREATER_THAN, $afterId)
            ->orderBy('id')
            ->setLimit(static::BATCH_SIZE);
    }

    /**
     * Whether the solicitation's cart was last updated at or before the cutoff, or carries no update time.
     *
     * A time that cannot be parsed keeps the solicitation, so nothing is deleted on a guess.
     *
     * @param SeQuraOrder $order
     * @param int $cutoff
     *
     * @return bool
     */
    protected function isStale(SeQuraOrder $order, int $cutoff): bool
    {
        $updatedAt = $order->getUnshippedCart()->getUpdatedAt();
        if ($updatedAt === null || $updatedAt === '') {
            return true;
        }

        $updatedAtTimestamp = strtotime($updatedAt);
        if ($updatedAtTimestamp === false) {
            Logger::logWarning(
                'Order cleanup kept a solicitation with an unreadable cart update time.',
                'Core',
                [
                    new LogContextData('reference', $order->getReference()),
                    new LogContextData('updatedAt', $updatedAt),
                ]
            );

            return false;
        }

        return $updatedAtTimestamp <= $cutoff;
    }

    /**
     * Deletes the given solicitations with one statement when the repository supports it, one by one otherwise.
     *
     * The statement repeats the shop order reference condition the solicitations were read with, so one that
     * became a shop order since it was read is kept.
     *
     * @param SeQuraOrder[] $orders
     * @param string $orderRef1Operator Operators::EQUALS or Operators::NULL, as the orders were read with.
     *
     * @return void
     *
     * @throws QueryFilterInvalidParamException
     */
    protected function deleteOrders(array $orders, string $orderRef1Operator): void
    {
        if (empty($orders)) {
            return;
        }

        if ($this->orderRepository instanceof ConditionallyDeletes) {
            $ids = array_map(function (SeQuraOrder $order) {
                return $order->getId();
            }, $orders);
            $this->orderRepository->deleteWhere(
                (new QueryFilter())
                    ->where('id', Operators::IN, $ids)
                    ->where('orderRef1', $orderRef1Operator, $orderRef1Operator === Operators::EQUALS ? '' : null)
            );

            return;
        }

        foreach ($orders as $order) {
            $this->orderRepository->delete($order);
        }
    }
}
