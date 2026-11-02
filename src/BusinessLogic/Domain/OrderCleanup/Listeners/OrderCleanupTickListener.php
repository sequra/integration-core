<?php

namespace SeQura\Core\BusinessLogic\Domain\OrderCleanup\Listeners;

use SeQura\Core\BusinessLogic\Domain\OrderCleanup\Services\OrderCleanupService;
use SeQura\Core\BusinessLogic\Domain\OrderCleanup\Tasks\OrderCleanupTask;
use SeQura\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use SeQura\Core\Infrastructure\ServiceRegister;
use SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException;
use SeQura\Core\Infrastructure\TaskExecution\Interfaces\Priority;
use SeQura\Core\Infrastructure\TaskExecution\QueueItem;
use SeQura\Core\Infrastructure\TaskExecution\QueueService;

/**
 * Class OrderCleanupTickListener
 *
 * Enqueues the order cleanup once a day, after a host has scheduled it.
 *
 * @package SeQura\Core\BusinessLogic\Domain\OrderCleanup\Listeners
 */
class OrderCleanupTickListener
{
    /**
     * Name of the queue the cleanup task runs in.
     */
    public const QUEUE_NAME = 'order-cleanup';

    /**
     * @return void
     *
     * @throws QueryFilterInvalidParamException
     * @throws QueueStorageUnavailableException
     */
    public static function handle(): void
    {
        $cleanupService = static::getOrderCleanupService();
        if (!$cleanupService->isDue() || static::isCleanupPending()) {
            return;
        }

        static::getQueueService()->enqueue(static::QUEUE_NAME, new OrderCleanupTask(), '', Priority::LOW);
        $cleanupService->advanceNextRun();
    }

    /**
     * Whether a cleanup task is still waiting or running, so a slow run does not pile up.
     *
     * @return bool
     */
    protected static function isCleanupPending(): bool
    {
        $latest = static::getQueueService()->findLatestByType(OrderCleanupTask::getClassName());

        return $latest !== null && \in_array($latest->getStatus(), [QueueItem::QUEUED, QueueItem::IN_PROGRESS], true);
    }

    /**
     * @return OrderCleanupService
     */
    protected static function getOrderCleanupService(): OrderCleanupService
    {
        return ServiceRegister::getService(OrderCleanupService::class);
    }

    /**
     * @return QueueService
     */
    protected static function getQueueService(): QueueService
    {
        return ServiceRegister::getService(QueueService::class);
    }
}
