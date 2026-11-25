<?php

namespace SeQura\Core\BusinessLogic\TransactionLog\Listeners;

use SeQura\Core\BusinessLogic\TransactionLog\Services\TransactionLogService;
use SeQura\Core\BusinessLogic\TransactionLog\Contracts\TransactionLogAwareInterface;
use SeQura\Core\Infrastructure\TaskExecution\Events\BaseQueueItemEvent;
use SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException;

/**
 * Class Listener
 *
 * @package SeQura\Core\BusinessLogic\TransactionLog\Listeners
 */
abstract class Listener
{
    /**
     * @var TransactionLogService
     */
    protected $transactionLogService;

    public function __construct(TransactionLogService $transactionLogService)
    {
        $this->transactionLogService = $transactionLogService;
    }

    /**
     * Manages transaction log on state change.
     *
     * @param BaseQueueItemEvent $event
     *
     * @throws QueueItemDeserializationException
     */
    public function handle(BaseQueueItemEvent $event): void
    {
        if (!$this->canHandle($event)) {
            return;
        }

        $this->doHandle($event);
    }

    /**
     * Handles the event.
     *
     * @param BaseQueueItemEvent $event
     *
     * @return void
     */
    abstract protected function doHandle(BaseQueueItemEvent $event): void;

    /**
     * Checks if event should be handled.
     *
     * @param BaseQueueItemEvent $event
     *
     * @return bool
     *
     * @throws QueueItemDeserializationException
     */
    protected function canHandle(BaseQueueItemEvent $event): bool
    {
        $task = $event->getQueueItem()->getTask();

        if ($task === null) {
            return false;
        }

        return $task instanceof TransactionLogAwareInterface;
    }

    /**
     * Retrieves transaction log service.
     *
     * @return TransactionLogService
     */
    protected function getService(): TransactionLogService
    {
        return $this->transactionLogService;
    }
}
