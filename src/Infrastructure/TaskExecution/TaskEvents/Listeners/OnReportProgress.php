<?php

namespace SeQura\Core\Infrastructure\TaskExecution\TaskEvents\Listeners;

use SeQura\Core\Infrastructure\ServiceRegister;
use SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException;
use SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException;
use SeQura\Core\Infrastructure\TaskExecution\QueueItem;
use SeQura\Core\Infrastructure\TaskExecution\QueueService;

/**
 * Class OnReportProgress
 *
 * @package SeQura\Core\Infrastructure\TaskExecution\TaskEvents\Listeners
 */
class OnReportProgress
{
    /**
     * Handles queue item progress change.
     *
     * @param QueueItem $queueItem
     * @param int $progressBasePoints
     *
     * @throws QueueStorageUnavailableException
     * @throws QueueItemDeserializationException
     */
    public static function handle(QueueItem $queueItem, int $progressBasePoints): void
    {
        $queue = static::getQueueService();
        $queue->updateProgress($queueItem, $progressBasePoints);
    }

    /**
     * Provides queue service.
     *
     * @return QueueService
     */
    protected static function getQueueService(): QueueService
    {
        return ServiceRegister::getService(QueueService::CLASS_NAME);
    }
}
