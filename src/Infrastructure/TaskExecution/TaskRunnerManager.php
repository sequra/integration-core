<?php

namespace SeQura\Core\Infrastructure\TaskExecution;

use SeQura\Core\Infrastructure\Configuration\Configuration;
use SeQura\Core\Infrastructure\ServiceRegister;
use SeQura\Core\Infrastructure\TaskExecution\Interfaces\TaskRunnerManager as BaseService;
use SeQura\Core\Infrastructure\TaskExecution\Interfaces\TaskRunnerWakeup;

class TaskRunnerManager implements BaseService
{
    /**
     * @var Configuration
     */
    protected $configuration;
    /**
     * @var TaskRunnerWakeup
     */
    protected $tasRunnerWakeupService;

    /**
     * Halts task runner.
     */
    public function halt()
    {
        $this->getConfiguration()->setTaskRunnerHalted(true);
    }

    /**
     * Resumes task execution.
     */
    public function resume()
    {
        $this->getConfiguration()->setTaskRunnerHalted(false);
        $this->getTaskRunnerWakeupService()->wakeup();
    }

    /**
     * Retrieves configuration.
     *
     * @return Configuration Configuration instance.
     */
    protected function getConfiguration()
    {
        if ($this->configuration === null) {
            $this->configuration = ServiceRegister::getService(Configuration::CLASS_NAME);
        }

        return $this->configuration;
    }

    /**
     * Retrieves task runner wakeup service.
     *
     * @return TaskRunnerWakeup Task runner wakeup instance.
     */
    protected function getTaskRunnerWakeupService()
    {
        if ($this->tasRunnerWakeupService === null) {
            $this->tasRunnerWakeupService = ServiceRegister::getService(TaskRunnerWakeup::CLASS_NAME);
        }

        return $this->tasRunnerWakeupService;
    }
}
