<?php

namespace SeQura\Core\Infrastructure\TaskExecution;

use SeQura\Core\Infrastructure\Configuration\Configuration;
use SeQura\Core\Infrastructure\ORM\Interfaces\QueueItemRepository;
use SeQura\Core\Infrastructure\ORM\QueryFilter\QueryFilter;
use SeQura\Core\Infrastructure\ORM\RepositoryRegistry;
use SeQura\Core\Infrastructure\ServiceRegister;
use SeQura\Core\Infrastructure\TaskExecution\Events\BeforeQueueStatusChangeEvent;
use SeQura\Core\Infrastructure\TaskExecution\Events\QueueItemAbortedEvent;
use SeQura\Core\Infrastructure\TaskExecution\Events\QueueItemCreatedEvent;
use SeQura\Core\Infrastructure\TaskExecution\Events\QueueItemEnqueuedEvent;
use SeQura\Core\Infrastructure\TaskExecution\Events\QueueItemFailedEvent;
use SeQura\Core\Infrastructure\TaskExecution\Events\QueueItemFinishedEvent;
use SeQura\Core\Infrastructure\TaskExecution\Events\QueueItemRequeuedEvent;
use SeQura\Core\Infrastructure\TaskExecution\Events\QueueItemStartedEvent;
use SeQura\Core\Infrastructure\TaskExecution\Events\QueueItemStateTransitionEventBus;
use SeQura\Core\Infrastructure\TaskExecution\Events\QueueStatusChangedEvent;
use SeQura\Core\Infrastructure\TaskExecution\Exceptions\AbortTaskExecutionException;
use SeQura\Core\Infrastructure\TaskExecution\Exceptions\ExecutionRequirementsNotMetException;
use SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException;
use SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueItemSaveException;
use SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException;
use SeQura\Core\Infrastructure\TaskExecution\Interfaces\Priority;
use SeQura\Core\Infrastructure\TaskExecution\Interfaces\TaskRunnerWakeup;
use SeQura\Core\Infrastructure\Utility\Events\Event;
use SeQura\Core\Infrastructure\Utility\Events\EventBus;
use SeQura\Core\Infrastructure\Utility\TimeProvider;
use BadMethodCallException;
use RuntimeException;

/**
 * Class Queue.
 *
 * @package SeQura\Core\Infrastructure\TaskExecution
 */
class QueueService
{
    /**
     * Fully qualified name of this class.
     */
    const CLASS_NAME = __CLASS__;
    /**
     * Maximum failure retries count
     */
    const MAX_RETRIES = 5;
    /**
     * A storage for task queue.
     *
     * @var RepositoryRegistry
     */
    protected $storage;
    /**
     * Time provider instance.
     *
     * @var TimeProvider
     */
    protected $timeProvider;
    /**
     * Task runner wakeup instance.
     *
     * @var TaskRunnerWakeup
     */
    protected $taskRunnerWakeup;
    /**
     * Configuration service instance.
     *
     * @var Configuration
     */
    protected $configService;

    /**
     * Updates status of a group of tasks.
     *
     * @param array $ids
     * @param string $status
     */
    public function batchStatusUpdate(array $ids, $status)
    {
        $this->getStorage()->batchStatusUpdate($ids, $status);
    }

    /**
     * Creates queue item.
     *
     * @param $queueName
     * @param Task $task
     * @param string $context
     * @param int $priority
     * @param int $parent
     *
     * @return QueueItem
     *
     * @throws QueueStorageUnavailableException
     */
    public function create($queueName, Task $task, $context = '', $priority = Priority::NORMAL, $parent = null)
    {
        $queueItem = $this->instantiate($task, $queueName, $context, $priority, $parent);
        $this->save($queueItem);
        $this->fireStateTransitionEvent(new QueueItemCreatedEvent($queueItem));

        return $queueItem;
    }

    /**
     * Enqueues queue item to a given queue and stores changes.
     *
     * @param string $queueName Name of a queue where queue item should be queued.
     * @param Task $task Task to enqueue.
     * @param string $context Task execution context. If integration supports multiple accounts (middleware
     *     integration) context based on account id should be provided. Failing to do this will result in global task
     *     context and unpredictable task execution.
     * @param int $priority
     *
     * @return QueueItem Created queue item.
     *
     * @throws QueueStorageUnavailableException When queue storage fails to save the item.
     */
    public function enqueue($queueName, Task $task, $context = '', $priority = Priority::NORMAL)
    {
        $queueItem = $this->instantiate($task, $queueName, $context, $priority);
        $queueItem->setStatus(QueueItem::QUEUED);
        $queueItem->setQueueTimestamp($this->getTimeProvider()->getCurrentLocalTime()->getTimestamp());
        $this->save($queueItem, array(), true, QueueItem::CREATED);
        $this->fireStateTransitionEvent(new QueueItemEnqueuedEvent($queueItem));

        $this->getTaskRunnerWakeup()->wakeup();

        return $queueItem;
    }

    /**
     * Validates that the execution requirements are met for the particular
     * Execution job.
     *
     * @param QueueItem $queueItem
     *
     * @throws ExecutionRequirementsNotMetException
     */
    public function validateExecutionRequirements(QueueItem $queueItem)
    {
    }

    /**
     * Starts task execution, puts queue item in "in_progress" state and stores queue item changes.
     *
     * @param QueueItem $queueItem Queue item to start.
     *
     * @throws QueueItemDeserializationException
     * @throws QueueStorageUnavailableException
     * @throws AbortTaskExecutionException
     */
    public function start(QueueItem $queueItem)
    {
        if ($queueItem->getStatus() !== QueueItem::QUEUED) {
            $this->throwIllegalTransitionException($queueItem->getStatus(), QueueItem::IN_PROGRESS);
        }

        $lastUpdateTimestamp = $queueItem->getLastUpdateTimestamp();

        $queueItem->setStatus(QueueItem::IN_PROGRESS);
        $queueItem->setStartTimestamp($this->getTimeProvider()->getCurrentLocalTime()->getTimestamp());
        $queueItem->setLastUpdateTimestamp($queueItem->getStartTimestamp());

        $this->save(
            $queueItem,
            array('status' => QueueItem::QUEUED, 'lastUpdateTimestamp' => $lastUpdateTimestamp),
            true,
            QueueItem::QUEUED
        );

        if ($queueItem->getTask() === null) {
            throw new QueueItemDeserializationException('Deserialized task is null.');
        }

        $this->fireStateTransitionEvent(new QueueItemStartedEvent($queueItem));
        $queueItem->getTask()->execute();
    }

    /**
     * Puts queue item in finished status and stores changes.
     *
     * @param QueueItem $queueItem Queue item to finish.
     *
     * @throws QueueStorageUnavailableException
     */
    public function finish(QueueItem $queueItem)
    {
        if ($queueItem->getStatus() !== QueueItem::IN_PROGRESS) {
            $this->throwIllegalTransitionException($queueItem->getStatus(), QueueItem::COMPLETED);
        }

        $queueItem->setStatus(QueueItem::COMPLETED);
        $queueItem->setFinishTimestamp($this->getTimeProvider()->getCurrentLocalTime()->getTimestamp());
        $queueItem->setProgressBasePoints(10000);

        $this->save(
            $queueItem,
            array('status' => QueueItem::IN_PROGRESS, 'lastUpdateTimestamp' => $queueItem->getLastUpdateTimestamp()),
            true,
            QueueItem::IN_PROGRESS
        );

        $this->fireStateTransitionEvent(new QueueItemFinishedEvent($queueItem));
    }

    /**
     * Returns queue item back to queue and sets updates last execution progress to current progress value.
     *
     * @param QueueItem $queueItem Queue item to requeue.
     *
     * @throws QueueStorageUnavailableException
     */
    public function requeue(QueueItem $queueItem)
    {
        if ($queueItem->getStatus() !== QueueItem::IN_PROGRESS) {
            $this->throwIllegalTransitionException($queueItem->getStatus(), QueueItem::QUEUED);
        }

        $lastExecutionProgress = $queueItem->getLastExecutionProgressBasePoints();

        $queueItem->setStatus(QueueItem::QUEUED);
        $queueItem->setStartTimestamp(null);
        $queueItem->setLastExecutionProgressBasePoints($queueItem->getProgressBasePoints());

        $this->save(
            $queueItem,
            array(
                'status' => QueueItem::IN_PROGRESS,
                'lastExecutionProgress' => $lastExecutionProgress,
                'lastUpdateTimestamp' => $queueItem->getLastUpdateTimestamp(),
            ),
            true,
            QueueItem::IN_PROGRESS
        );

        $this->fireStateTransitionEvent(new QueueItemRequeuedEvent($queueItem));
    }

    /**
     * Returns queue item back to queue and increments retries count.
     * When max retries count is reached puts item in failed status.
     *
     * @param QueueItem $queueItem Queue item to fail.
     * @param string $failureDescription Verbal description of failure.
     * @param bool $force Ignores max retries and forces failure.
     *
     * @throws QueueItemDeserializationException
     * @throws QueueStorageUnavailableException
     */
    public function fail(QueueItem $queueItem, $failureDescription, $force = false)
    {
        if ($queueItem->getStatus() !== QueueItem::IN_PROGRESS) {
            $this->throwIllegalTransitionException($queueItem->getStatus(), QueueItem::FAILED);
        }

        $task = null;
        try {
            $task = $queueItem->getTask();
        } catch (\Exception $e) {
        }

        if (!$force && $task === null) {
            throw new QueueItemDeserializationException("Failed to deserialize task.");
        }

        $queueItem->setRetries($queueItem->getRetries() + 1);
        $queueItem->setFailureDescription(
            ($queueItem->getFailureDescription() ? ($queueItem->getFailureDescription() . "\n") : '')
            . 'Attempt ' . $queueItem->getRetries() . ': ' . $failureDescription
        );

        if ($force || $queueItem->getRetries() > $this->getMaxRetries()) {
            $queueItem->setStatus(QueueItem::FAILED);
            $queueItem->setFailTimestamp($this->getTimeProvider()->getCurrentLocalTime()->getTimestamp());
            if ($task !== null) {
                $task->onFail();
            }
        } else {
            $queueItem->setStatus(QueueItem::QUEUED);
            $queueItem->setStartTimestamp(null);
        }

        $this->save(
            $queueItem,
            array(
                'status' => QueueItem::IN_PROGRESS,
                'lastExecutionProgress' => $queueItem->getLastExecutionProgressBasePoints(),
                'lastUpdateTimestamp' => $queueItem->getLastUpdateTimestamp(),
            ),
            true,
            QueueItem::IN_PROGRESS
        );

        if ($queueItem->getStatus() === QueueItem::FAILED && $queueItem->getParentId()) {
            $parent = $this->find($queueItem->getParentId());
            if ($parent === null) {
                throw new RuntimeException("Parent not found");
            }

            $this->fail($parent, "SubJob failed.", true);
        }

        $this->fireStateTransitionEvent(new QueueItemFailedEvent($queueItem, $failureDescription));
    }

    /**
     * Aborts the queue item. Aborted queue item will not be started again.
     *
     * @param QueueItem $queueItem Queue item to abort.
     * @param string $abortDescription Verbal description of the reason for abortion.
     *
     * @throws BadMethodCallException Queue item must be in "in_progress" status for abort method.
     * @throws QueueStorageUnavailableException
     * @throws QueueItemDeserializationException
     */
    public function abort(QueueItem $queueItem, $abortDescription)
    {
        if (!in_array($queueItem->getStatus(), [QueueItem::CREATED, QueueItem::QUEUED, QueueItem::IN_PROGRESS])) {
            $this->throwIllegalTransitionException($queueItem->getStatus(), QueueItem::ABORTED);
        }

        $task = $queueItem->getTask();
        if ($task === null) {
            throw new QueueItemDeserializationException("Failed to deserialize task.");
        }

        $task->onAbort();

        $queueItem->setStatus(QueueItem::ABORTED);
        $queueItem->setFailTimestamp($this->getTimeProvider()->getCurrentLocalTime()->getTimestamp());
        $queueItem->setFailureDescription(
            ($queueItem->getFailureDescription() ? ($queueItem->getFailureDescription() . "\n") : '')
            . 'Attempt ' . ($queueItem->getRetries() + 1) . ': ' . $abortDescription
        );
        $this->save(
            $queueItem,
            array(
                'lastExecutionProgress' => $queueItem->getLastExecutionProgressBasePoints(),
                'lastUpdateTimestamp' => $queueItem->getLastUpdateTimestamp(),
            ),
            true,
            $queueItem->getStatus()
        );


        if ($queueItem->getParentId()) {
            $parent = $this->find($queueItem->getParentId());
            if ($parent === null) {
                throw new RuntimeException("Parent not found");
            }

            $this->abort($parent, "SubJob aborted.");
        }

        $this->fireStateTransitionEvent(new QueueItemAbortedEvent($queueItem, $abortDescription));
    }

    /**
     * Updates queue item progress.
     *
     * @param QueueItem $queueItem Queue item to be updated.
     * @param int $progress New progress.
     *
     * @throws QueueStorageUnavailableException
     */
    public function updateProgress(QueueItem $queueItem, $progress)
    {
        if ($queueItem->getStatus() !== QueueItem::IN_PROGRESS) {
            throw new BadMethodCallException('Progress reported for not started queue item.');
        }

        if ($progress === 10000) {
            $this->finish($queueItem);
            return;
        }

        $lastExecutionProgress = $queueItem->getLastExecutionProgressBasePoints();
        $lastUpdateTimestamp = $queueItem->getLastUpdateTimestamp();

        $queueItem->setProgressBasePoints($progress);
        $queueItem->setLastUpdateTimestamp($this->getTimeProvider()->getCurrentLocalTime()->getTimestamp());

        $this->save(
            $queueItem,
            array(
                'status' => QueueItem::IN_PROGRESS,
                'lastExecutionProgress' => $lastExecutionProgress,
                'lastUpdateTimestamp' => $lastUpdateTimestamp,
            )
        );
    }

    /**
     * Keeps passed queue item alive by setting last update timestamp.
     *
     * @param QueueItem $queueItem Queue item to keep alive.
     *
     * @throws QueueStorageUnavailableException
     */
    public function keepAlive(QueueItem $queueItem)
    {
        $lastExecutionProgress = $queueItem->getLastExecutionProgressBasePoints();
        $lastUpdateTimestamp = $queueItem->getLastUpdateTimestamp();
        $queueItem->setLastUpdateTimestamp($this->getTimeProvider()->getCurrentLocalTime()->getTimestamp());
        $this->save(
            $queueItem,
            array(
                'status' => QueueItem::IN_PROGRESS,
                'lastExecutionProgress' => $lastExecutionProgress,
                'lastUpdateTimestamp' => $lastUpdateTimestamp,
            )
        );
    }

    /**
     * Finds queue item by Id.
     *
     * @param int $id Id of a queue item to find.
     *
     * @noinspection PhpDocMissingThrowsInspection
     *
     * @return QueueItem|null Queue item if found; otherwise, NULL.
     */
    public function find($id)
    {
        $filter = new QueryFilter();
        /**
         * @noinspection PhpUnhandledExceptionInspection
        */
        $filter->where('id', '=', $id);

        return $this->getStorage()->selectOne($filter);
    }

    /**
     * Finds latest queue item by type.
     *
     * @param string $type Type of a queue item to find.
     * @param string $context Task scope restriction, default is global scope.
     *
     * @noinspection PhpDocMissingThrowsInspection
     *
     * @return QueueItem|null Queue item if found; otherwise, NULL.
     */
    public function findLatestByType($type, $context = '')
    {
        $filter = new QueryFilter();
        /**
         * @noinspection PhpUnhandledExceptionInspection
        */
        $filter->where('taskType', '=', $type);
        if (!empty($context)) {
            /**
             * @noinspection PhpUnhandledExceptionInspection
            */
            $filter->where('context', '=', $context);
        }

        /**
         * @noinspection PhpUnhandledExceptionInspection
        */
        $filter->orderBy('queueTime', 'DESC');

        return $this->getStorage()->selectOne($filter);
    }

    /**
     * @noinspection PhpDocMissingThrowsInspection
     *
     * Finds queue items with status "in_progress".
     *
     * @return QueueItem[] Running queue items.
     */
    public function findRunningItems()
    {
        $filter = new QueryFilter();
        /**
         * @noinspection PhpUnhandledExceptionInspection
        */
        $filter->where('status', '=', QueueItem::IN_PROGRESS);

        return $this->getStorage()->select($filter);
    }

    /**
     * Finds list of earliest queued queue items per queue.
     * Only queues that doesn't have running tasks are taken in consideration.
     * Returned queue items are ordered in the descending priority.
     *
     * @param int $limit Result set limit. By default max 10 earliest queue items will be returned.
     *
     * @return QueueItem[] An array of found queue items.
     */
    public function findOldestQueuedItems($limit = 10)
    {
        $result = array();
        $currentLimit = $limit;

        foreach (QueueItem::getAvailablePriorities() as $priority) {
            $batch = $this->getStorage()->findOldestQueuedItems($priority, $currentLimit);
            $result[] = $batch;

            if (($currentLimit -= count($batch)) <= 0) {
                break;
            }
        }

        $result = !empty($result) ? array_merge(...$result) : $result;

        return array_slice($result, 0, $limit);
    }

    /**
     * @param Event $event
     */
    public function fireStateTransitionEvent(Event $event)
    {
        $bus = ServiceRegister::getService(QueueItemStateTransitionEventBus::CLASS_NAME);
        $bus->fire($event);
    }

    /**
     * Creates or updates given queue item using storage service. If queue item id is not set, new queue item will be
     * created; otherwise, update will be performed.
     *
     * @param QueueItem $queueItem Item to save.
     * @param array $additionalWhere List of key/value pairs to set in where clause when saving queue item.
     * @param bool $reportStateChange Indicates whether to invoke a status change event.
     * @param string $previousState If event should be invoked, indicates the previous state.
     *
     * @return int Id of saved queue item.
     *
     * @throws QueueStorageUnavailableException
     */
    protected function save(
        QueueItem $queueItem,
        array $additionalWhere = array(),
        $reportStateChange = false,
        $previousState = ''
    ) {
        try {
            if ($reportStateChange) {
                $this->reportBeforeStatusChange($queueItem, $previousState);
            }

            $id = $this->getStorage()->saveWithCondition($queueItem, $additionalWhere);
            $queueItem->setId($id);

            if ($reportStateChange) {
                $this->reportStatusChange($queueItem, $previousState);
            }

            return $id;
        } catch (QueueItemSaveException $exception) {
            throw new QueueStorageUnavailableException('Unable to update the task.', $exception);
        }
    }

    /**
     * Fires event for before status change.
     *
     * @param QueueItem $queueItem Queue item with is about to change status.
     * @param string $previousState Previous state. MUST be one of the states defined as constants in @see QueueItem.
     */
    protected function reportBeforeStatusChange(QueueItem $queueItem, $previousState)
    {
        /**
         * @var EventBus $eventBus
        */
        $eventBus = ServiceRegister::getService(EventBus::CLASS_NAME);
        $eventBus->fire(new BeforeQueueStatusChangeEvent($queueItem, $previousState));
    }

    /**
     * Fires event for status change.
     *
     * @param QueueItem $queueItem Queue item with changed status.
     * @param string $previousState Previous state. MUST be one of the states defined as constants in @see QueueItem.
     */
    protected function reportStatusChange(QueueItem $queueItem, $previousState)
    {
        /**
         * @var EventBus $eventBus
        */
        $eventBus = ServiceRegister::getService(EventBus::CLASS_NAME);
        $eventBus->fire(new QueueStatusChangedEvent($queueItem, $previousState));
    }

    /**
     * @noinspection PhpDocMissingThrowsInspection
     *
     * Gets task storage instance.
     *
     * @return QueueItemRepository Task storage instance.
     */
    protected function getStorage()
    {
        if ($this->storage === null) {
            /**
             * @noinspection PhpUnhandledExceptionInspection
            */
            $this->storage = RepositoryRegistry::getQueueItemRepository();
        }

        return $this->storage;
    }

    /**
     * Gets time provider instance.
     *
     * @return TimeProvider Time provider instance.
     */
    protected function getTimeProvider()
    {
        if ($this->timeProvider === null) {
            $this->timeProvider = ServiceRegister::getService(TimeProvider::CLASS_NAME);
        }

        return $this->timeProvider;
    }

    /**
     * Gets task runner wakeup instance.
     *
     * @return TaskRunnerWakeup Task runner wakeup instance.
     */
    protected function getTaskRunnerWakeup()
    {
        if ($this->taskRunnerWakeup === null) {
            $this->taskRunnerWakeup = ServiceRegister::getService(TaskRunnerWakeup::CLASS_NAME);
        }

        return $this->taskRunnerWakeup;
    }

    /**
     * Gets configuration service instance.
     *
     * @return Configuration Configuration service instance.
     */
    protected function getConfigService()
    {
        if ($this->configService === null) {
            $this->configService = ServiceRegister::getService(Configuration::CLASS_NAME);
        }

        return $this->configService;
    }

    /**
     * Prepares exception message and throws exception.
     *
     * @param string $fromStatus A status form which status change is attempts.
     * @param string $toStatus A status to which status change is attempts.
     *
     * @throws BadMethodCallException
     */
    protected function throwIllegalTransitionException($fromStatus, $toStatus)
    {
        throw new BadMethodCallException(
            sprintf(
                'Illegal queue item state transition from "%s" to "%s"',
                $fromStatus,
                $toStatus
            )
        );
    }

    /**
     * Returns maximum number of retries.
     *
     * @return int Number of retries.
     */
    protected function getMaxRetries()
    {
        $configurationValue = $this->getConfigService()->getMaxTaskExecutionRetries();

        return $configurationValue !== null ? $configurationValue : self::MAX_RETRIES;
    }

    /**
     * Instantiates queue item for a task.
     *
     * @param Task $task
     * @param string $queueName
     * @param string $context
     * @param int $priority
     * @param int | null $parent
     *
     * @return QueueItem
     */
    protected function instantiate(Task $task, $queueName, $context, $priority, $parent = null)
    {
        $queueItem = new QueueItem($task);
        $queueItem->setQueueName($queueName);
        $queueItem->setContext($context);
        $queueItem->setPriority($priority);
        $queueItem->setStatus(QueueItem::CREATED);
        $queueItem->setParentId($parent);

        return $queueItem;
    }
}
