<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\TaskExecution\Doubles;

use SeQura\Core\BusinessLogic\Domain\Integration\TaskExecution\BusinessTaskInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\TaskExecution\TaskExecutorInterface;

class RecordingTaskExecutor implements TaskExecutorInterface
{
    public function enqueue(BusinessTaskInterface $task): void
    {
    }

    public function scheduleDelayed(BusinessTaskInterface $task, int $delaySeconds): void
    {
    }
}
