<?php

namespace SeQura\Core\BusinessLogic\OrderCleanup\Listeners;

use SeQura\Core\BusinessLogic\Domain\OrderCleanup\Services\OrderCleanupService;
use SeQura\Core\BusinessLogic\OrderCleanup\Tasks\OrderCleanupTask;
use SeQura\Core\Infrastructure\Logger\LogContextData;
use SeQura\Core\Infrastructure\Logger\Logger;
use SeQura\Core\Infrastructure\ServiceRegister;
use SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException;
use SeQura\Core\Infrastructure\TaskExecution\Interfaces\Priority;
use SeQura\Core\Infrastructure\TaskExecution\QueueItem;
use SeQura\Core\Infrastructure\TaskExecution\QueueService;
use Throwable;

/**
 * Class OrderCleanupTickListener
 *
 * Enqueues the order cleanup once a day, after a host has scheduled it.
 *
 * @package SeQura\Core\BusinessLogic\OrderCleanup\Listeners
 */
class OrderCleanupTickListener
{
    /**
     * Name of the queue the cleanup task runs in.
     */
    public const QUEUE_NAME = 'order-cleanup';

    /**
     * Enqueues the cleanup when it is due. A failure is logged and never leaves the tick, so it cannot keep the
     * task runner from waking up again.
     *
     * @return void
     */
    public static function handle(): void
    {
        try {
            static::enqueueWhenDue();
        } catch (Throwable $exception) {
            Logger::logError(
                'Order cleanup could not be enqueued.',
                'Core',
                [new LogContextData('error', $exception->getMessage())]
            );
        }
    }

    /**
     * Moves the next run ahead before anything else, so a tick that comes while the cleanup is enqueued sees it
     * is not due, and a run skipped because an earlier cleanup is still queued or running stays skipped instead
     * of starting right after that cleanup ends.
     *
     * @return void
     *
     * @throws QueueStorageUnavailableException
     */
    protected static function enqueueWhenDue(): void
    {
        $cleanupService = static::getOrderCleanupService();
        if (!$cleanupService->isDue()) {
            return;
        }

        $cleanupService->advanceNextRun();
        if (static::isCleanupPending()) {
            return;
        }

        static::getQueueService()->enqueue(static::QUEUE_NAME, new OrderCleanupTask(), '', Priority::LOW);
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
