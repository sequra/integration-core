<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents\TaskExecution;

use SeQura\Core\BusinessLogic\Domain\Integration\TaskExecution\BusinessTaskInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\TaskExecution\TaskExecutorInterface;
use Throwable;

class RecordingTaskExecutor implements TaskExecutorInterface
{
    /**
     * @var BusinessTaskInterface[]
     */
    public $enqueued = [];

    /**
     * @var BusinessTaskInterface[]
     */
    public $delayed = [];

    /**
     * @var Throwable|null
     */
    private $failure;

    public function __construct(?Throwable $failure = null)
    {
        $this->failure = $failure;
    }

    public function enqueue(BusinessTaskInterface $task): void
    {
        if ($this->failure !== null) {
            throw $this->failure;
        }

        $this->enqueued[] = $task;
    }

    public function scheduleDelayed(BusinessTaskInterface $task, int $delaySeconds): void
    {
        $this->delayed[] = $task;
    }
}
