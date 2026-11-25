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
    * @var string
    */
    protected const ABORT_DESCRIPTION = 'Order update action not supported on SeQura.';

    /**
     * @var QueueItemAbortedEvent
     */
    protected $event;

    /**
     * @inheritdoc
     */
    protected function save(): void
    {
        $this->transactionLog->setReason($this->event->getAbortDescription());
        $this->transactionLog->setFailureDescription(self::ABORT_DESCRIPTION);

        parent::save();
    }
}
