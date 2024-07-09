<?php

namespace SeQura\Core\Infrastructure\TaskExecution\Events;

use SeQura\Core\Infrastructure\TaskExecution\QueueItem;
use SeQura\Core\Infrastructure\Utility\Events\Event;

/**
 * Class BeforeQueueStatusChangeEvent.
 *
 * @package SeQura\Core\Infrastructure\Scheduler
 */
class BeforeQueueStatusChangeEvent extends Event
{
    /**
     * Fully qualified name of this class.
     */
    const CLASS_NAME = __CLASS__;
    /**
     * Queue item.
     *
     * @var QueueItem
     */
    protected $queueItem;
    /**
     * Previous state of queue item.
     *
     * @var string
     */
    protected $previousState;

    /**
     * TaskProgressEvent constructor.
     *
     * @param QueueItem $queueItem Queue item with changed status.
     * @param string $previousState Previous state. MUST be one of the states defined as constants in @see QueueItem.
     */
    public function __construct(QueueItem $queueItem, $previousState)
    {
        $this->queueItem = $queueItem;
        $this->previousState = $previousState;
    }

    /**
     * Gets Queue item.
     *
     * @return QueueItem Queue item.
     */
    public function getQueueItem()
    {
        return $this->queueItem;
    }

    /**
     * Gets previous state.
     *
     * @return string Previous state..
     */
    public function getPreviousState()
    {
        return $this->previousState;
    }
}
