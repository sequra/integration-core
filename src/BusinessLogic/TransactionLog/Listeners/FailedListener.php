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
    * @var string
    */
    protected const FAILURE_DESCRIPTION = 'Order update action not supported on SeQura.';

    /**
     * @var QueueItemFailedEvent
     */
    protected $event;

    /**
     * @inheritdoc
     */
    protected function save(): void
    {
        $this->transactionLog->setReason($this->event->getFailureDescription());
        $this->transactionLog->setFailureDescription(self::FAILURE_DESCRIPTION);

        parent::save();
    }
}
