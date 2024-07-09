<?php

namespace SeQura\Core\Infrastructure\TaskExecution;

use SeQura\Core\Infrastructure\Configuration\Configuration;
use SeQura\Core\Infrastructure\Configuration\ConfigurationManager;
use SeQura\Core\Infrastructure\Logger\Logger;
use SeQura\Core\Infrastructure\ServiceRegister;
use SeQura\Core\Infrastructure\TaskExecution\Exceptions\ProcessStarterSaveException;
use SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException;
use SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException;
use SeQura\Core\Infrastructure\TaskExecution\Exceptions\TaskRunnerStatusChangeException;
use SeQura\Core\Infrastructure\TaskExecution\Exceptions\TaskRunnerStatusStorageUnavailableException;
use SeQura\Core\Infrastructure\TaskExecution\Interfaces\TaskRunnerStatusStorage;
use SeQura\Core\Infrastructure\TaskExecution\Interfaces\TaskRunnerWakeup;
use SeQura\Core\Infrastructure\Utility\TimeProvider;
use Exception;

/**
 * Class TaskRunner.
 *
 * @package SeQura\Core\Infrastructure\TaskExecution
 */
class TaskRunner
{
    /**
     * Fully qualified name of this class.
     */
    const CLASS_NAME = __CLASS__;
    /**
     * Automatic task runner wakeup delay in seconds
     */
    const WAKEUP_DELAY = 5;
    /**
     * Defines minimal time in seconds between two consecutive alive since updates.
     */
    const TASK_RUNNER_KEEP_ALIVE_PERIOD = 2;
    /**
     * Runner guid.
     *
     * @var string
     */
    protected $guid;
    /**
     * Service.
     *
     * @var QueueService
     */
    protected $queueService;
    /**
     * Service.
     *
     * @var TaskRunnerStatusStorage
     */
    protected $runnerStorage;
    /**
     * Service.
     *
     * @var Configuration
     */
    protected $configurationService;
    /**
     * Service.
     *
     * @var TimeProvider
     */
    protected $timeProvider;
    /**
     * Service.
     *
     * @var TaskRunnerWakeup
     */
    protected $taskWakeup;
    /**
     * Configuration manager.
     *
     * @var ConfigurationManager Configuration manager instance.
     */
    protected $configurationManager;
    /**
     * Defines when was the task runner alive since time step last updated at.
     *
     * @var int
     */
    protected $aliveSinceUpdatedAt = 0;
    /**
     * Sleep time in seconds with microsecond precision.
     *
     * @var float
     */
    protected $batchSleepTime = 0.0;

    /**
     * Sets task runner guid.
     *
     * @param string $guid Runner guid to set.
     */
    public function setGuid($guid)
    {
        $this->guid = $guid;
    }

    /**
     * Starts task runner lifecycle.
     */
    public function run()
    {
        try {
            $this->keepAlive();

            if ($this->getConfigurationService()->isTaskRunnerHalted()) {
                $this->logDebug(array('Message' => 'Task runner is currently halted.'));
                return;
            }

            $this->logDebug(array('Message' => 'Task runner: lifecycle started.'));

            if ($this->isCurrentRunnerAlive()) {
                $this->failOrRequeueExpiredTasks();
                $this->startOldestQueuedItems();
            }

            $this->keepAlive();

            $this->wakeup();

            $this->logDebug(array('Message' => 'Task runner: lifecycle ended.'));
        } catch (Exception $ex) {
            $this->logDebug(
                array(
                    'Message' => 'Fail to run task runner. Unexpected error occurred.',
                    'ExceptionMessage' => $ex->getMessage(),
                    'ExceptionTrace' => $ex->getTraceAsString(),
                )
            );
        }
    }

    /**
     * Fails or re-queues expired tasks.
     *
     * @throws QueueItemDeserializationException
     * @throws QueueStorageUnavailableException
     * @throws TaskRunnerStatusStorageUnavailableException
     */
    protected function failOrRequeueExpiredTasks()
    {
        $this->logDebug(array('Message' => 'Task runner: expired tasks cleanup started.'));

        $runningItems = $this->getQueue()->findRunningItems();
        if (!$this->isCurrentRunnerAlive()) {
            return;
        }

        $this->keepAlive();

        while ($runningItem = array_shift($runningItems)) {
            if (!$this->isCurrentRunnerAlive()) {
                break;
            }

            try {
                $this->failOrRequeueExpiredTask($runningItem);
            } catch (QueueItemDeserializationException $e) {
                // If task can't be deserialized we should fail it immediately since there is nothing we can dao to recover
                $this->getQueue()->fail(
                    $runningItem,
                    sprintf(
                        'Task %s is invalid or corrupted. Task deserialization failed.',
                        $runningItem->getId()
                    ),
                    true
                );
            }

            $this->keepAlive();
        }
    }

    /**
     * Fails or re-queues provided task if it expired.
     *
     * @param QueueItem $item
     *
     * @throws QueueItemDeserializationException
     * @throws QueueStorageUnavailableException
     */
    protected function failOrRequeueExpiredTask(QueueItem $item)
    {
        if (!$this->isItemExpired($item)) {
            return;
        }

        $this->logMessageFor($item, 'Task runner: Expired task detected.');
        $this->getConfigurationManager()->setContext($item->getContext());
        if ($item->getProgressBasePoints() > $item->getLastExecutionProgressBasePoints()) {
            $this->logMessageFor($item, 'Task runner: Task requeue for execution continuation.');
            $this->getQueue()->requeue($item);
        } else {
            $item->reconfigureTask();
            $this->getQueue()->fail(
                $item,
                sprintf(
                    'Task %s failed due to extended inactivity period.',
                    $this->getItemDescription($item)
                )
            );
        }
    }

    /**
     * Starts oldest queue item from all queues respecting following list of criteria:
     *      - Queue must be without already running queue items
     *      - For one queue only one (oldest queued) item should be started
     *      - Number of running tasks must NOT be greater than maximal allowed by integration configuration.
     *
     * @throws ProcessStarterSaveException
     * @throws QueueItemDeserializationException
     * @throws TaskRunnerStatusStorageUnavailableException
     */
    protected function startOldestQueuedItems()
    {
        $this->keepAlive();

        $this->logDebug(array('Message' => 'Task runner: available task detection started.'));

        // Calculate how many queue items can be started
        $maxRunningTasks = $this->getConfigurationService()->getMaxStartedTasksLimit();
        $alreadyRunningItems = $this->getQueue()->findRunningItems();
        $numberOfAvailableSlots = $maxRunningTasks - count($alreadyRunningItems);
        if ($numberOfAvailableSlots <= 0) {
            $this->logDebug(array('Message' => 'Task runner: max number of active tasks reached.'));

            return;
        }

        $this->keepAlive();

        $items = $this->getQueue()->findOldestQueuedItems($numberOfAvailableSlots);

        $this->keepAlive();

        if (!$this->isCurrentRunnerAlive()) {
            return;
        }

        $asyncStarterBatchSize = $this->getConfigurationService()->getAsyncStarterBatchSize();
        $batchStarter = new AsyncBatchStarter($asyncStarterBatchSize);
        foreach ($items as $item) {
            $this->logMessageFor($item, 'Task runner: Adding task to a batch starter for async execution.');
            $batchStarter->addRunner(new QueueItemStarter($item->getId()));
        }

        $this->keepAlive();

        if (!$this->isCurrentRunnerAlive()) {
            return;
        }

        $this->logDebug(array('Message' => 'Task runner: Starting batch starter execution.'));
        $startTime = $this->getTimeProvider()->getMicroTimestamp();
        $batchStarter->run();
        $endTime = $this->getTimeProvider()->getMicroTimestamp();

        $this->keepAlive();

        $averageRequestTime = ($endTime - $startTime) / $asyncStarterBatchSize;
        $this->batchSleepTime = $batchStarter->getWaitTime($averageRequestTime);

        $this->logDebug(
            array(
                'Message' => 'Task runner: Batch starter execution finished.',
                'ExecutionTime' => ($endTime - $startTime) . 's',
                'AverageRequestTime' => $averageRequestTime . 's',
                'StartedItems' => count($items),
            )
        );
    }

    /**
     * Executes wakeup on runner.
     *
     * @throws TaskRunnerStatusChangeException
     * @throws TaskRunnerStatusStorageUnavailableException
     */
    protected function wakeup()
    {
        $this->logDebug(array('Message' => 'Task runner: starting self deactivation.'));

        for ($i = 0; $i < $this->getWakeupDelay(); $i++) {
            $this->getTimeProvider()->sleep(1);
            $this->keepAlive();
        }

        $this->getRunnerStorage()->setStatus(TaskRunnerStatus::createNullStatus());

        $this->logDebug(array('Message' => 'Task runner: sending task runner wakeup signal.'));
        $this->getTaskWakeup()->wakeup();
    }

    /**
     * Updates alive since time stamp of the task runner.
     *
     * @throws TaskRunnerStatusStorageUnavailableException
     */
    protected function keepAlive()
    {
        $currentTime = $this->getTimeProvider()->getCurrentLocalTime()->getTimestamp();
        if (($currentTime - $this->aliveSinceUpdatedAt) < self::TASK_RUNNER_KEEP_ALIVE_PERIOD) {
            return;
        }

        $this->getConfigurationService()->setTaskRunnerStatus($this->guid, $currentTime);
        $this->aliveSinceUpdatedAt = $currentTime;
    }

    /**
     * Checks whether current runner is alive.
     *
     * @return bool TRUE if runner is alive; otherwise, FALSE.
     *
     * @throws TaskRunnerStatusStorageUnavailableException
     */
    protected function isCurrentRunnerAlive()
    {
        $runnerStatus = $this->getRunnerStorage()->getStatus();
        $runnerExpired = $runnerStatus->isExpired();
        $runnerGuidIsCorrect = $this->guid === $runnerStatus->getGuid();

        if ($runnerExpired) {
            $this->logWarning(array('Message' => 'Task runner: Task runner started but it is expired.'));
        }

        if (!$runnerGuidIsCorrect) {
            $this->logWarning(array('Message' => 'Task runner: Task runner started but it is not active anymore.'));
        }

        return !$runnerExpired && $runnerGuidIsCorrect;
    }

    /**
     * Checks whether queue item is expired.
     *
     * @param QueueItem $item Queue item for checking.
     *
     * @return bool TRUE if queue item expired; otherwise, FALSE.
     *
     * @throws QueueItemDeserializationException
     */
    protected function isItemExpired(QueueItem $item)
    {
        $currentTimestamp = $this->getTimeProvider()->getCurrentLocalTime()->getTimestamp();
        $maxTaskInactivityPeriod = $item->getTask()->getMaxInactivityPeriod();

        return ($item->getLastUpdateTimestamp() + $maxTaskInactivityPeriod) < $currentTimestamp;
    }

    /**
     * Gets queue item description.
     *
     * @param QueueItem $item Queue item.
     *
     * @return string Description of queue item.
     *
     * @throws QueueItemDeserializationException
     */
    protected function getItemDescription(QueueItem $item)
    {
        return "{$item->getId()}({$item->getTaskType()})";
    }

    /**
     * Gets @return QueueService Queue service instance.
     *
     * @see QueueService service instance.
     */
    protected function getQueue()
    {
        if ($this->queueService === null) {
            $this->queueService = ServiceRegister::getService(QueueService::CLASS_NAME);
        }

        return $this->queueService;
    }

    /**
     * Gets @return TaskRunnerStatusStorage Service instance.
     *
     * @see TaskRunnerStatusStorageInterface service instance.
     */
    protected function getRunnerStorage()
    {
        if ($this->runnerStorage === null) {
            $this->runnerStorage = ServiceRegister::getService(TaskRunnerStatusStorage::CLASS_NAME);
        }

        return $this->runnerStorage;
    }

    /**
     * Retrieves configuration manager.
     *
     * @return ConfigurationManager Configuration manager instance.
     */
    public function getConfigurationManager()
    {
        if ($this->configurationManager === null) {
            $this->configurationManager = ServiceRegister::getService(ConfigurationManager::CLASS_NAME);
        }

        return $this->configurationManager;
    }

    /**
     * Gets @return Configuration Service instance.
     *
     * @see Configuration service instance.
     */
    protected function getConfigurationService()
    {
        if ($this->configurationService === null) {
            $this->configurationService = ServiceRegister::getService(Configuration::CLASS_NAME);
        }

        return $this->configurationService;
    }

    /**
     * Gets @return TimeProvider Service instance.
     *
     * @see TimeProvider instance.
     */
    protected function getTimeProvider()
    {
        if ($this->timeProvider === null) {
            $this->timeProvider = ServiceRegister::getService(TimeProvider::CLASS_NAME);
        }

        return $this->timeProvider;
    }

    /**
     * Gets @return TaskRunnerWakeup Service instance.
     *
     * @see TaskRunnerWakeupInterface service instance.
     */
    protected function getTaskWakeup()
    {
        if ($this->taskWakeup === null) {
            $this->taskWakeup = ServiceRegister::getService(TaskRunnerWakeup::CLASS_NAME);
        }

        return $this->taskWakeup;
    }

    /**
     * Returns wakeup delay in seconds
     *
     * @return int Wakeup delay in seconds.
     */
    protected function getWakeupDelay()
    {
        $configurationValue = $this->getConfigurationService()->getTaskRunnerWakeupDelay();

        $minimalSleepTime = $configurationValue !== null ? $configurationValue : self::WAKEUP_DELAY;

        return $minimalSleepTime + ceil($this->batchSleepTime);
    }

    /**
     * Logs message and queue item details.
     *
     * @param QueueItem $queueItem Queue item.
     * @param string $message Message to be logged.
     *
     * @throws QueueItemDeserializationException
     */
    protected function logMessageFor(QueueItem $queueItem, $message)
    {
        $this->logDebug(
            array(
                'RunnerGuid' => $this->guid,
                'Message' => $message,
                'TaskId' => $queueItem->getId(),
                'TaskType' => $queueItem->getTaskType(),
                'TaskRetries' => $queueItem->getRetries(),
                'TaskProgressBasePoints' => $queueItem->getProgressBasePoints(),
                'TaskLastExecutionProgressBasePoints' => $queueItem->getLastExecutionProgressBasePoints(),
            )
        );
    }

    /**
     * Helper methods to encapsulate debug level logging.
     *
     * @param array $debugContent Array of debug content for logging.
     */
    protected function logDebug(array $debugContent)
    {
        $debugContent['RunnerGuid'] = $this->guid;
        Logger::logDebug($debugContent['Message'], 'Core', $debugContent);
    }

    /**
     * Helper methods to encapsulate warning level logging.
     *
     * @param array $debugContent Array of debug content for logging.
     */
    protected function logWarning(array $debugContent)
    {
        $debugContent['RunnerGuid'] = $this->guid;
        Logger::logWarning($debugContent['Message'], 'Core', $debugContent);
    }
}
