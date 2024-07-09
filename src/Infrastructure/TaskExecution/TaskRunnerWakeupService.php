<?php

namespace SeQura\Core\Infrastructure\TaskExecution;

use SeQura\Core\Infrastructure\Http\Exceptions\HttpRequestException;
use SeQura\Core\Infrastructure\Logger\Logger;
use SeQura\Core\Infrastructure\ServiceRegister;
use SeQura\Core\Infrastructure\TaskExecution\Exceptions\ProcessStarterSaveException;
use SeQura\Core\Infrastructure\TaskExecution\Exceptions\TaskRunnerStatusChangeException;
use SeQura\Core\Infrastructure\TaskExecution\Exceptions\TaskRunnerStatusStorageUnavailableException;
use SeQura\Core\Infrastructure\TaskExecution\Interfaces\AsyncProcessService;
use SeQura\Core\Infrastructure\TaskExecution\Interfaces\TaskRunnerStatusStorage;
use SeQura\Core\Infrastructure\TaskExecution\Interfaces\TaskRunnerWakeup;
use SeQura\Core\Infrastructure\Utility\GuidProvider;
use SeQura\Core\Infrastructure\Utility\TimeProvider;
use Exception;

/**
 * Class TaskRunnerWakeupService.
 *
 * @package SeQura\Core\Infrastructure\TaskExecution
 */
class TaskRunnerWakeupService implements TaskRunnerWakeup
{
    /**
     * Service instance.
     *
     * @var AsyncProcessStarterService
     */
    protected $asyncProcessStarter;
    /**
     * Service instance.
     *
     * @var RunnerStatusStorage
     */
    protected $runnerStatusStorage;
    /**
     * Service instance.
     *
     * @var TimeProvider
     */
    protected $timeProvider;
    /**
     * Service instance.
     *
     * @var GuidProvider
     */
    protected $guidProvider;

    /**
     * Wakes up @see TaskRunner instance asynchronously if active instance is not already running.
     */
    public function wakeup()
    {
        try {
            $this->doWakeup();
        } catch (TaskRunnerStatusChangeException $ex) {
            Logger::logDebug(
                'Fail to wakeup task runner. Runner status storage failed to set new active state.',
                'Core',
                array(
                    'ExceptionMessage' => $ex->getMessage(),
                    'ExceptionTrace' => $ex->getTraceAsString(),
                )
            );
        } catch (TaskRunnerStatusStorageUnavailableException $ex) {
            Logger::logDebug(
                'Fail to wakeup task runner. Runner status storage unavailable.',
                'Core',
                array(
                    'ExceptionMessage' => $ex->getMessage(),
                    'ExceptionTrace' => $ex->getTraceAsString(),
                )
            );
        } catch (Exception $ex) {
            Logger::logDebug(
                'Fail to wakeup task runner. Unexpected error occurred.',
                'Core',
                array(
                    'ExceptionMessage' => $ex->getMessage(),
                    'ExceptionTrace' => $ex->getTraceAsString(),
                )
            );
        }
    }

    /**
     * Executes wakeup of queued task.
     *
     * @throws ProcessStarterSaveException
     * @throws TaskRunnerStatusChangeException
     * @throws TaskRunnerStatusStorageUnavailableException
     * @throws HttpRequestException
     */
    protected function doWakeup()
    {
        $runnerStatus = $this->getRunnerStorage()->getStatus();
        $currentGuid = $runnerStatus->getGuid();
        if (!empty($currentGuid) && !$runnerStatus->isExpired()) {
            return;
        }

        if ($runnerStatus->isExpired()) {
            $this->runnerStatusStorage->setStatus(TaskRunnerStatus::createNullStatus());
            Logger::logDebug('Expired task runner detected, wakeup component will start new instance.');
        }

        $guid = $this->getGuidProvider()->generateGuid();

        $this->runnerStatusStorage->setStatus(
            new TaskRunnerStatus(
                $guid,
                $this->getTimeProvider()->getCurrentLocalTime()->getTimestamp()
            )
        );

        $this->getAsyncProcessStarter()->start(new TaskRunnerStarter($guid));
    }

    /**
     * Gets instance of @return TaskRunnerStatusStorage Service instance.
     *
     * @see TaskRunnerStatusStorageInterface.
     */
    protected function getRunnerStorage()
    {
        if ($this->runnerStatusStorage === null) {
            $this->runnerStatusStorage = ServiceRegister::getService(TaskRunnerStatusStorage::CLASS_NAME);
        }

        return $this->runnerStatusStorage;
    }

    /**
     * Gets instance of @return GuidProvider Service instance.
     *
     * @see GuidProvider.
     */
    protected function getGuidProvider()
    {
        if ($this->guidProvider === null) {
            $this->guidProvider = ServiceRegister::getService(GuidProvider::CLASS_NAME);
        }

        return $this->guidProvider;
    }

    /**
     * Gets instance of @return TimeProvider Service instance.
     *
     * @see TimeProvider.
     */
    protected function getTimeProvider()
    {
        if ($this->timeProvider === null) {
            $this->timeProvider = ServiceRegister::getService(TimeProvider::CLASS_NAME);
        }

        return $this->timeProvider;
    }

    /**
     * Gets instance of @return AsyncProcessStarterService Service instance.
     *
     * @see AsyncProcessStarterService.
     */
    protected function getAsyncProcessStarter()
    {
        if ($this->asyncProcessStarter === null) {
            $this->asyncProcessStarter = ServiceRegister::getService(AsyncProcessService::CLASS_NAME);
        }

        return $this->asyncProcessStarter;
    }
}
