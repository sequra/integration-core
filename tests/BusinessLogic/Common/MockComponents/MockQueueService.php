<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use SeQura\Core\Infrastructure\TaskExecution\Interfaces\Priority;
use SeQura\Core\Infrastructure\TaskExecution\QueueItem;
use SeQura\Core\Infrastructure\TaskExecution\QueueService;
use SeQura\Core\Infrastructure\TaskExecution\Task;

/**
 * Class MockQueueService
 *
 * @package SeQura\Core\Tests\BusinessLogic\Common\MockComponents
 */
class MockQueueService extends QueueService
{
    public $queueItems = [];

    /**
     * @param $queueName
     * @param Task $task
     * @param string $context
     * @param int $priority
     * @param int|null $parent
     *
     * @return QueueItem
     */
    public function create(
        $queueName,
        Task $task,
        $context = '',
        $priority = Priority::NORMAL,
        $parent = null
    ): QueueItem {
        $item = new QueueItem($task, $context);
        $item->setId(count($this->queueItems));
        $this->queueItems[] = $item;

        return $item;
    }

    public function batchStatusUpdate(array $ids, $status): void
    {
        // Intentionally not implemented.
    }
}
