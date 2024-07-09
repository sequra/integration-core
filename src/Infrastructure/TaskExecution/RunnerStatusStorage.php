<?php

namespace SeQura\Core\Infrastructure\TaskExecution;

use SeQura\Core\Infrastructure\Configuration\Configuration;
use SeQura\Core\Infrastructure\ServiceRegister;
use SeQura\Core\Infrastructure\TaskExecution\Exceptions\TaskRunnerStatusChangeException;
use SeQura\Core\Infrastructure\TaskExecution\Exceptions\TaskRunnerStatusStorageUnavailableException;
use SeQura\Core\Infrastructure\TaskExecution\Interfaces\TaskRunnerStatusStorage;

/**
 * Class RunnerStatusStorage.
 *
 * @package SeQura\Core\Infrastructure\TaskExecution
 */
class RunnerStatusStorage implements TaskRunnerStatusStorage
{
    /**
     * Configuration service instance.
     *
     * @var Configuration
     */
    protected $configService;

    /**
     * Returns task runner status.
     *
     * @return TaskRunnerStatus Task runner status instance.
     *
     * @throws TaskRunnerStatusStorageUnavailableException
     */
    public function getStatus()
    {
        $result = $this->getConfigService()->getTaskRunnerStatus();
        if (empty($result)) {
            $this->getConfigService()->setTaskRunnerStatus('', null);

            return TaskRunnerStatus::createNullStatus();
        }

        return new TaskRunnerStatus($result['guid'], $result['timestamp']);
    }

    /**
     * Sets task runner status.
     *
     * @param TaskRunnerStatus $status Status instance.
     *
     * @throws TaskRunnerStatusChangeException
     * @throws TaskRunnerStatusStorageUnavailableException
     */
    public function setStatus(TaskRunnerStatus $status)
    {
        $this->checkTaskRunnerStatusChangeAvailability($status);
        $this->getConfigService()->setTaskRunnerStatus($status->getGuid(), $status->getAliveSinceTimestamp());
    }

    /**
     * Checks if task runner can change availability status.
     *
     * @param TaskRunnerStatus $status Status instance.
     *
     * @throws TaskRunnerStatusChangeException
     * @throws TaskRunnerStatusStorageUnavailableException
     */
    protected function checkTaskRunnerStatusChangeAvailability(TaskRunnerStatus $status)
    {
        $currentGuid = $this->getStatus()->getGuid();
        $guidForUpdate = $status->getGuid();

        if (!empty($currentGuid) && !empty($guidForUpdate) && $currentGuid !== $guidForUpdate) {
            throw new TaskRunnerStatusChangeException(
                'Task runner with guid: ' . $guidForUpdate . ' can not change the status.'
            );
        }
    }

    /**
     * Gets instance of @return Configuration Service instance.
     *
     * @see Configuration service.
     */
    protected function getConfigService()
    {
        if ($this->configService === null) {
            $this->configService = ServiceRegister::getService(Configuration::CLASS_NAME);
        }

        return $this->configService;
    }
}
