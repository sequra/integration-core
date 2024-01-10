<?php

namespace SeQura\Core\BusinessLogic\TransactionLog\Listeners;

use SeQura\Core\Infrastructure\TaskExecution\Events\QueueItemAbortedEvent;

/**
 * Class AbortedListener
 *
 * @package SeQura\Core\BusinessLogic\TransactionLog\Listeners
 */
class AbortedListener extends UpdateListener
{
    /**
     * @var QueueItemAbortedEvent
     */
    protected $event;

    /**
     * @inheritdoc
     */
    protected function save(): void
    {
        $this->transactionLog->setFailureDescription($this->event->getAbortDescription());

        parent::save();
    }
}
