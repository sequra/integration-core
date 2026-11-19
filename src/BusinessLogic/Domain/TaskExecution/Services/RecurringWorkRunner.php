<?php

namespace SeQura\Core\BusinessLogic\Domain\TaskExecution\Services;

use SeQura\Core\BusinessLogic\Domain\Integration\TaskExecution\RecurringWorkLockInterface;
use SeQura\Core\BusinessLogic\Domain\OrderReport\Service\OrderReportScheduler;
use SeQura\Core\BusinessLogic\Domain\OrderCleanup\Services\OrderCleanupScheduler;
use SeQura\Core\Infrastructure\Logger\LogContextData;
use SeQura\Core\Infrastructure\Logger\Logger;
use SeQura\Core\Infrastructure\ServiceRegister;
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
     * @var OrderReportScheduler
     */
    private $orderReportScheduler;

    /**
     * @param RecurringWorkLockInterface $lock
     * @param OrderReportScheduler $orderReportScheduler
     */
    public function __construct(RecurringWorkLockInterface $lock, OrderReportScheduler $orderReportScheduler)
    {
        $this->lock = $lock;
        $this->orderReportScheduler = $orderReportScheduler;
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
            $this->runOrderCleanup();
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
            $this->orderReportScheduler->scheduleDue();
        } catch (Throwable $exception) {
            Logger::logError(
                'Order reports could not be enqueued.',
                'Core',
                [new LogContextData('error', $exception->getMessage())]
            );
        }
    }

    /**
     * @return void
     */
    private function runOrderCleanup(): void
    {
        try {
            ServiceRegister::getService(OrderCleanupScheduler::class)->scheduleWhenDue();
        } catch (Throwable $exception) {
            Logger::logError(
                'Order cleanup could not be enqueued.',
                'Core',
                [new LogContextData('error', $exception->getMessage())]
            );
        }
    }
}
