<?php

namespace SeQura\Core\BusinessLogic\TransactionLog\Listeners;

use SeQura\Core\Infrastructure\TaskExecution\Events\BaseQueueItemEvent;
use SeQura\Core\Infrastructure\TaskExecution\Events\QueueItemStartedEvent;
use SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException;

/**
 * Class LoadListener
 *
 * @package SeQura\Core\BusinessLogic\TransactionLog\Listeners
 */
class LoadListener extends Listener
{
    /**
     * @var QueueItemStartedEvent
     */
    protected $event;

    /**
     * @inheritDoc
     *
     * @throws QueueItemDeserializationException
     */
    protected function doHandle(BaseQueueItemEvent $event): void
    {
        $this->getService()->load($event->getQueueItem());
    }
}
