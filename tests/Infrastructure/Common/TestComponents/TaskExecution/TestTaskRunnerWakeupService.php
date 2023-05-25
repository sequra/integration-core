<?php

namespace SeQura\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution;

use SeQura\Core\Infrastructure\TaskExecution\TaskRunnerWakeupService;

class TestTaskRunnerWakeupService extends TaskRunnerWakeupService
{
    private $callHistory = array();

    public function getMethodCallHistory($methodName)
    {
        return !empty($this->callHistory[$methodName]) ? $this->callHistory[$methodName] : array();
    }

    public function resetCallHistory()
    {
        $this->callHistory = array();
    }

    public function wakeup()
    {
        $this->callHistory['wakeup'][] = array();
    }
}
