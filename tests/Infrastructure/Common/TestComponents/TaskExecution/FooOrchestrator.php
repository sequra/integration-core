<?php

namespace SeQura\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution;

use SeQura\Core\Infrastructure\TaskExecution\Composite\ExecutionDetails;
use SeQura\Core\Infrastructure\TaskExecution\Composite\Orchestrator;

class FooOrchestrator extends Orchestrator
{
    const SUB_JOB_COUNT = 3;
    /**
     * List of subtasks created and managed by the orchestrator
     *
     * @var ExecutionDetails[]
     */
    public $taskList = [];

    protected function getSubTask()
    {
        if (count($this->taskList) === self::SUB_JOB_COUNT) {
            return null;
        }

        return $this->createSubJob(new FooTask());
    }
}
