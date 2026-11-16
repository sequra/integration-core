<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\OrderReport\Services;

use SeQura\Core\BusinessLogic\Domain\Integration\TaskExecution\BusinessTaskInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\TaskExecution\TaskExecutorInterface;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Throwable;

class RecordingExecutor implements TaskExecutorInterface
{
    /**
     * @var BusinessTaskInterface[]
     */
    public $tasks = [];

    /**
     * @var string[]
     */
    public $stores = [];

    /**
     * @var int
     */
    public $delayed = 0;

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
        if ($this->failure && $store === $this->failingStore) {
            throw $this->failure;
        }

        $this->tasks[] = $task;
        $this->stores[] = $store;
    }

    public function scheduleDelayed(BusinessTaskInterface $task, int $delaySeconds): void
    {
        $this->delayed++;
    }
}
