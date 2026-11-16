<?php

namespace SeQura\Core\BusinessLogic\Domain\OrderCleanup\Services;

use SeQura\Core\BusinessLogic\Domain\Integration\TaskExecution\TaskExecutorInterface;
use SeQura\Core\BusinessLogic\Domain\OrderCleanup\Tasks\OrderCleanupTask;
use SeQura\Core\Infrastructure\Logger\LogContextData;
use SeQura\Core\Infrastructure\Logger\Logger;
use Throwable;

/**
 * @package SeQura\Core\BusinessLogic\Domain\OrderCleanup\Services
 */
class OrderCleanupScheduler
{
    /**
     * @var OrderCleanupService
     */
    protected $cleanupService;

    /**
     * @var TaskExecutorInterface
     */
    protected $taskExecutor;

    /**
     * @param OrderCleanupService $cleanupService
     * @param TaskExecutorInterface $taskExecutor
     */
    public function __construct(OrderCleanupService $cleanupService, TaskExecutorInterface $taskExecutor)
    {
        $this->cleanupService = $cleanupService;
        $this->taskExecutor = $taskExecutor;
    }

    /**
     * @return void
     * @throws Throwable
     */
    public function scheduleWhenDue(): void
    {
        $dueRun = $this->cleanupService->claimDueRun();
        if ($dueRun === null) {
            return;
        }

        try {
            $this->taskExecutor->enqueue(new OrderCleanupTask());
        } catch (Throwable $exception) {
            $this->restoreNextRun($dueRun);

            throw $exception;
        }
    }

    /**
     * @param int $dueRun
     *
     * @return void
     */
    protected function restoreNextRun(int $dueRun): void
    {
        try {
            $this->cleanupService->schedule($dueRun, true);
        } catch (Throwable $exception) {
            Logger::logError(
                'Order cleanup could not move its next run back to the run that was due.',
                'Core',
                [new LogContextData('error', $exception->getMessage())]
            );
        }
    }
}
