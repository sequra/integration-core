<?php

namespace SeQura\Core\BusinessLogic\Domain\TaskExecution\Services;

use SeQura\Core\BusinessLogic\Domain\Integration\TaskExecution\RecurringWorkLockInterface;
use SeQura\Core\BusinessLogic\Domain\OrderReport\Listeners\TickEventListener;
use SeQura\Core\BusinessLogic\OrderCleanup\Listeners\OrderCleanupTickListener;
use SeQura\Core\Infrastructure\Logger\LogContextData;
use SeQura\Core\Infrastructure\Logger\Logger;
use Throwable;

/**
 * @package SeQura\Core\BusinessLogic\Domain\TaskExecution\Services
 */
class RecurringWorkRunner
{
    /**
     * @var RecurringWorkLockInterface
     */
    private $lock;

    /**
     * @param RecurringWorkLockInterface $lock
     */
    public function __construct(RecurringWorkLockInterface $lock)
    {
        $this->lock = $lock;
    }

    /**
     * @return void
     */
    public function runDue(): void
    {
        if (!$this->lock->tryAcquire()) {
            return;
        }

        try {
            $this->runOrderReports();
            OrderCleanupTickListener::handle();
        } finally {
            $this->lock->release();
        }
    }

    /**
     * @return void
     */
    private function runOrderReports(): void
    {
        try {
            TickEventListener::handle();
        } catch (Throwable $exception) {
            Logger::logError(
                'Order reports could not be enqueued.',
                'Core',
                [new LogContextData('error', $exception->getMessage())]
            );
        }
    }
}
