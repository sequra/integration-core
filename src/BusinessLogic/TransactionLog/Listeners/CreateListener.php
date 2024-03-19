<?php

namespace SeQura\Core\BusinessLogic\TransactionLog\Listeners;

use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\OrderNotFoundException;
use SeQura\Core\Infrastructure\TaskExecution\Events\BaseQueueItemEvent;
use SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException;

/**
 * Class CreateListener
 *
 * @package SeQura\Core\BusinessLogic\TransactionLog\Listeners
 */
class CreateListener extends Listener
{
    /**
     * @inheritDoc
     *
     * @throws QueueItemDeserializationException
     * @throws OrderNotFoundException
     */
    protected function doHandle(BaseQueueItemEvent $event): void
    {
        $queueItem = $event->getQueueItem();

        if ($queueItem->getParentId() !== null) {
            return;
        }

        if ($this->getService()->hasQueueItem($queueItem)) {
            $this->getService()->load($queueItem);

            return;
        }

        $this->getService()->create($queueItem);
    }
}
