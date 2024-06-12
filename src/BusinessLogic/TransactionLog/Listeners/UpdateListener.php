<?php

namespace SeQura\Core\BusinessLogic\TransactionLog\Listeners;

use DateTime;
use Exception;
use SeQura\Core\BusinessLogic\DataAccess\TransactionLog\Entities\TransactionLog;
use SeQura\Core\BusinessLogic\TransactionLog\Contracts\TransactionLogAwareInterface;
use SeQura\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use SeQura\Core\Infrastructure\TaskExecution\Events\BaseQueueItemEvent;
use SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException;
use SeQura\Core\Infrastructure\TaskExecution\QueueItem;

/**
 * Class UpdateListener
 *
 * @package SeQura\Core\BusinessLogic\TransactionLog\Listeners
 */
class UpdateListener extends Listener
{
    /**
     * @var TransactionLog
     */
    protected $transactionLog;

    /**
     * @var BaseQueueItemEvent
     */
    protected $event;

    /**
     * @var QueueItem
     */
    protected $queueItem;

    /**
     * @inheritDoc
     *
     * @throws Exception
     */
    protected function doHandle(BaseQueueItemEvent $event): void
    {
        $this->deleteOldLogs();
        $this->init($event);

        if ($this->queueItem->getStatus() === QueueItem::COMPLETED) {
            $this->delete();

            return;
        }

        $this->transactionLog->setQueueStatus($this->queueItem->getStatus());
        $this->save();
    }

    /**
     * @throws QueueItemDeserializationException
     */
    protected function init(BaseQueueItemEvent $event): void
    {
        $this->event = $event;
        $this->queueItem = $this->extractQueueItem();

        /**
        * @var TransactionLogAwareInterface $task
        */
        $task = $this->queueItem->getTask();
        $this->transactionLog = $task->getTransactionLog();
    }

    /**
     * @inheritdoc
     */
    protected function canHandle(BaseQueueItemEvent $event): bool
    {
        if (!parent::canHandle($event)) {
            return false;
        }

        $queueItem = $event->getQueueItem();

        /**
 * @var TransactionLogAwareInterface $task
*/
        $task = $queueItem->getTask();

        return !(!$task || !$task->getTransactionLog());
    }

    /**
     * @return void
     */
    protected function save(): void
    {
        $this->getService()->update($this->transactionLog);
    }

    /**
     * @return void
     *
     * @throws QueryFilterInvalidParamException
     */
    protected function delete(): void
    {
        $this->getService()->delete($this->transactionLog->getId());
    }

    /**
     * @return QueueItem
     */
    protected function extractQueueItem(): QueueItem
    {
        return $this->event->getQueueItem();
    }

    /**
     * Deletes TransactionLogs that are older than one month.
     *
     * @return void
     *
     * @throws Exception
     */
    protected function deleteOldLogs(): void
    {
        $currentDate = new DateTime();
        $this->getService()->deleteLogs($currentDate->modify('-1 month'), 5000);
    }
}
