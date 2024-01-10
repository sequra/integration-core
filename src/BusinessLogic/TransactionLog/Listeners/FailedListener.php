<?php

namespace SeQura\Core\BusinessLogic\TransactionLog\Listeners;

use SeQura\Core\Infrastructure\TaskExecution\Events\QueueItemFailedEvent;

/**
 * Class FailedListener
 *
 * @package SeQura\Core\BusinessLogic\TransactionLog\Listeners
 */
class FailedListener extends UpdateListener
{
    /**
     * @var QueueItemFailedEvent
     */
    protected $event;

    /**
     * @inheritdoc
     */
    protected function save(): void
    {
        $this->transactionLog->setFailureDescription($this->event->getFailureDescription());

        parent::save();
    }
}
