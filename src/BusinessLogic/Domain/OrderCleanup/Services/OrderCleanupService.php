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
     * Deletes the solicitations without a shop order whose cart was last updated a day ago or earlier,
     * or carries no update time at all.
     *
     * @return int Number of deleted solicitations.
     *
     * @throws QueryFilterInvalidParamException
     */
    public function cleanup(): int
    {
        $cutoff = $this->timeProvider->getCurrentLocalTime()->modify('-1 day')->getTimestamp();
        $deleted = 0;
        $offset = 0;

        do {
            /**
             * @var SeQuraOrder[] $orders
             */
            $orders = $this->orderRepository->select($this->getSolicitationsFilter($offset));
            $staleOrders = array_filter($orders, function (SeQuraOrder $order) use ($cutoff) {
                return $this->isStale($order, $cutoff);
            });

            $this->deleteOrders($staleOrders);
            $deleted += \count($staleOrders);
            $offset += \count($orders) - \count($staleOrders);
        } while (\count($orders) === static::BATCH_SIZE);

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
     * Moves the next cleanup run one day ahead of now.
     *
     * @return void
     *
     * @throws QueryFilterInvalidParamException
     */
    public function advanceNextRun(): void
    {
        $this->configurationManager->saveConfigValue(
            static::NEXT_RUN_CONFIG_KEY,
            $this->timeProvider->getCurrentLocalTime()->modify('+1 day')->getTimestamp(),
            false
        );
    }

    /**
     * Returns the filter selecting one batch of solicitations that have no shop order.
     *
     * @param int $offset
     *
     * @return QueryFilter
     *
     * @throws QueryFilterInvalidParamException
     */
    protected function getSolicitationsFilter(int $offset): QueryFilter
    {
        return (new QueryFilter())
            ->where('orderRef1', Operators::NULL)
            ->orWhere('orderRef1', Operators::EQUALS, '')
            ->orderBy('id')
            ->setLimit(static::BATCH_SIZE)
            ->setOffset($offset);
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
     * @param SeQuraOrder[] $orders
     *
     * @return void
     *
     * @throws QueryFilterInvalidParamException
     */
    protected function deleteOrders(array $orders): void
    {
        if (empty($orders)) {
            return;
        }

        if ($this->orderRepository instanceof ConditionallyDeletes) {
            $ids = array_map(function (SeQuraOrder $order) {
                return $order->getId();
            }, array_values($orders));
            $this->orderRepository->deleteWhere((new QueryFilter())->where('id', Operators::IN, $ids));

            return;
        }

        foreach ($orders as $order) {
            $this->orderRepository->delete($order);
        }
    }
}
