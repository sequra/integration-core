<?php

namespace SeQura\Core\Infrastructure\TaskExecution;

use SeQura\Core\Infrastructure\Logger\Logger;
use SeQura\Core\Infrastructure\Serializer\Interfaces\Serializable;
use SeQura\Core\Infrastructure\Serializer\Serializer;
use SeQura\Core\Infrastructure\ServiceRegister;
use SeQura\Core\Infrastructure\TaskExecution\Exceptions\TaskRunnerRunException;
use SeQura\Core\Infrastructure\TaskExecution\Exceptions\TaskRunnerStatusStorageUnavailableException;
use SeQura\Core\Infrastructure\TaskExecution\Interfaces\Runnable;
use SeQura\Core\Infrastructure\TaskExecution\Interfaces\TaskRunnerStatusStorage;
use SeQura\Core\Infrastructure\TaskExecution\Interfaces\TaskRunnerWakeup;
use SeQura\Core\Infrastructure\TaskExecution\TaskEvents\TickEvent;
use SeQura\Core\Infrastructure\Utility\Events\EventBus;
use Exception;

/**
 * Class TaskRunnerStarter.
 *
 * @package SeQura\Core\Infrastructure\TaskExecution
 */
class TaskRunnerStarter implements Runnable
{
    /**
     * Unique runner guid.
     *
     * @var string
     */
    protected $guid;
    /**
     * Instance of task runner status storage.
     *
     * @var TaskRunnerStatusStorage
     */
    protected $runnerStatusStorage;
    /**
     * Instance of task runner.
     *
     * @var TaskRunner
     */
    protected $taskRunner;
    /**
     * Instance of task runner wakeup service.
     *
     * @var TaskRunnerWakeup
     */
    protected $taskWakeup;

    /**
     * TaskRunnerStarter constructor.
     *
     * @param string $guid Unique runner guid.
     */
    public function __construct($guid)
    {
        $this->guid = $guid;
    }

    /**
     * Transforms array into an serializable object,
     *
     * @param array $array Data that is used to instantiate serializable object.
     *
     * @return Serializable
     *      Instance of serialized object.
     */
    public static function fromArray(array $array)
    {
        return new static($array['guid']);
    }

    /**
     * Transforms serializable object into an array.
     *
     * @return array Array representation of a serializable object.
     */
    public function toArray()
    {
        return array('guid' => $this->guid);
    }

    /**
     * @inheritDoc
     */
    public function __serialize()
    {
        return $this->toArray();
    }

    /**
     * @inheritDoc
     */
    public function __unserialize($data)
    {
        $this->guid = $data['guid'];
    }

    /**
     * String representation of object.
     *
     * @inheritdoc
     */
    public function serialize()
    {
        return Serializer::serialize(array($this->guid));
    }

    /**
     * Constructs the object.
     *
     * @inheritdoc
     */
    public function unserialize($serialized)
    {
        list($this->guid) = Serializer::unserialize($serialized);
    }

    /**
     * Get unique runner guid.
     *
     * @return string Unique runner string.
     */
    public function getGuid()
    {
        return $this->guid;
    }

    /**
     * Starts synchronously currently active task runner instance.
     */
    public function run()
    {
        try {
            $this->doRun();
        } catch (TaskRunnerStatusStorageUnavailableException $ex) {
            Logger::logError(
                'Failed to run task runner. Runner status storage unavailable.',
                'Core',
                array('ExceptionMessage' => $ex->getMessage())
            );
            Logger::logDebug(
                'Failed to run task runner. Runner status storage unavailable.',
                'Core',
                array(
                    'ExceptionMessage' => $ex->getMessage(),
                    'ExceptionTrace' => $ex->getTraceAsString(),
                )
            );
        } catch (TaskRunnerRunException $ex) {
            Logger::logInfo($ex->getMessage());
            Logger::logDebug($ex->getMessage(), 'Core', array('ExceptionTrace' => $ex->getTraceAsString()));
        } catch (Exception $ex) {
            Logger::logError(
                'Failed to run task runner. Unexpected error occurred.',
                'Core',
                array('ExceptionMessage' => $ex->getMessage())
            );
            Logger::logDebug(
                'Failed to run task runner. Unexpected error occurred.',
                'Core',
                array(
                    'ExceptionMessage' => $ex->getMessage(),
                    'ExceptionTrace' => $ex->getTraceAsString(),
                )
            );
        }
    }

    /**
     * Runs task execution.
     *
     * @throws TaskRunnerRunException
     * @throws TaskRunnerStatusStorageUnavailableException
     */
    protected function doRun()
    {
        $runnerStatus = $this->getRunnerStorage()->getStatus();
        if ($this->guid !== $runnerStatus->getGuid()) {
            throw new TaskRunnerRunException('Failed to run task runner. Runner guid is not set as active.');
        }

        if ($runnerStatus->isExpired()) {
            $this->getTaskWakeup()->wakeup();
            throw new TaskRunnerRunException('Failed to run task runner. Runner is expired.');
        }

        $this->getTaskRunner()->setGuid($this->guid);
        $this->getTaskRunner()->run();

        /**
         * @var EventBus $eventBus
        */
        $eventBus = ServiceRegister::getService(EventBus::CLASS_NAME);
        $eventBus->fire(new TickEvent());

        // Send wakeup signal when runner is completed.
        $this->getTaskWakeup()->wakeup();
    }

    /**
     * Gets task runner status storage instance.
     *
     * @return TaskRunnerStatusStorage Instance of runner status storage service.
     */
    protected function getRunnerStorage()
    {
        if ($this->runnerStatusStorage === null) {
            $this->runnerStatusStorage = ServiceRegister::getService(TaskRunnerStatusStorage::CLASS_NAME);
        }

        return $this->runnerStatusStorage;
    }

    /**
     * Gets task runner instance.
     *
     * @return TaskRunner Instance of runner service.
     */
    protected function getTaskRunner()
    {
        if ($this->taskRunner === null) {
            $this->taskRunner = ServiceRegister::getService(TaskRunner::CLASS_NAME);
        }

        return $this->taskRunner;
    }

    /**
     * Gets task runner wakeup instance.
     *
     * @return TaskRunnerWakeup Instance of runner wakeup service.
     */
    protected function getTaskWakeup()
    {
        if ($this->taskWakeup === null) {
            $this->taskWakeup = ServiceRegister::getService(TaskRunnerWakeup::CLASS_NAME);
        }

        return $this->taskWakeup;
    }
}
