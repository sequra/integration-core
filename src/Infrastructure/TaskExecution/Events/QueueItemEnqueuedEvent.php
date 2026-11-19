<?php

namespace SeQura\Core\Infrastructure\TaskExecution\Events;

use SeQura\Core\Infrastructure\TaskExecution\Task;

/**
 * Class QueueItemEnqueuedEvent
 *
 * @package SeQura\Core\Infrastructure\TaskExecution\Events
 */
class QueueItemEnqueuedEvent extends BaseQueueItemEvent
{
    /**
     * @var string
     */
    protected $queueName;
    /**
     * @var Task
     */
    protected $task;
    /**
     * @var string
     */
    protected $context;
    /**
     * @var int
     */
    protected $priority;

    /**
     * @return string
     */
    public function getQueueName()
    {
        return $this->getQueueItem()->getQueueName();
    }

    /**
     * @return Task
     */
    public function getTask()
    {
        return $this->getQueueItem()->getTask();
    }

    /**
     * @return string
     */
    public function getContext()
    {
        return $this->getQueueItem()->getContext();
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return $this->getQueueItem()->getPriority();
    }
}
