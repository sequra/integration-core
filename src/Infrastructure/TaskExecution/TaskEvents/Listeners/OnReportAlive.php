<?php

namespace SeQura\Core\Infrastructure\TaskExecution\TaskEvents\Listeners;

use SeQura\Core\Infrastructure\ServiceRegister;
use SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException;
use SeQura\Core\Infrastructure\TaskExecution\QueueItem;
use SeQura\Core\Infrastructure\TaskExecution\QueueService;
use RuntimeException;

/**
 * Class OnReportAlive
 *
 * @package SeQura\Core\Infrastructure\TaskExecution\TaskEvents\Listeners
 */
class OnReportAlive
{
    /**
     * Handles report alive event.
     *
     * @param QueueItem $queueItem
     *
     * @throws QueueStorageUnavailableException
     */
    public static function handle(QueueItem $queueItem)
    {
        $queue = static::getQueue();
        $queue->keepAlive($queueItem);
        if ($queueItem->getParentId() === null) {
            return;
        }

        $parent = $queue->find($queueItem->getParentId());

        if ($parent === null) {
            throw new RuntimeException("Parent not available.");
        }

        $queue->keepAlive($parent);
    }

    /**
     * Provides queue service.
     *
     * @return QueueService
     */
    protected static function getQueue()
    {
        return ServiceRegister::getService(QueueService::CLASS_NAME);
    }
}
