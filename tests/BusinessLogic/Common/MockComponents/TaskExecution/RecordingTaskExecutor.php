<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents\TaskExecution;

use SeQura\Core\BusinessLogic\Domain\Integration\TaskExecution\BusinessTaskInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\TaskExecution\TaskExecutorInterface;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
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
     * @var string[]
     */
    public $stores = [];

    /**
     * @var Throwable|null
     */
    private $failure;

    /**
     * @var string|null
     */
    private $failingStore;

    public function __construct(?Throwable $failure = null, ?string $failingStore = null)
    {
        $this->failure = $failure;
        $this->failingStore = $failingStore;
    }

    public function enqueue(BusinessTaskInterface $task): void
    {
        $store = StoreContext::getInstance()->getStoreId();
        if ($this->failure !== null && ($this->failingStore === null || $store === $this->failingStore)) {
            throw $this->failure;
        }

        $this->enqueued[] = $task;
        $this->stores[] = $store;
    }

    public function scheduleDelayed(BusinessTaskInterface $task, int $delaySeconds): void
    {
        $this->delayed[] = $task;
    }
}
