<?php

namespace SeQura\Core\BusinessLogic\TransactionLog\Services;

use ReflectionClass;
use SeQura\Core\BusinessLogic\DataAccess\TransactionLog\Entities\TransactionLog;
use SeQura\Core\BusinessLogic\Domain\Integration\TaskExecution\BusinessTaskInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\TaskExecution\TaskExecutorInterface;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\OrderNotFoundException;
use SeQura\Core\BusinessLogic\TransactionLog\Contracts\TransactionLogAwareInterface;
use SeQura\Core\Infrastructure\Utility\TimeProvider;
use Throwable;

/**
 * @package SeQura\Core\BusinessLogic\TransactionLog\Services
 */
class TransactionLogRecorder
{
    private const ORDER_FAILURE_DESCRIPTION = 'Order update action not supported on SeQura.';
    private const PURGE_BATCH_SIZE = 5000;

    /**
     * @var TransactionLogService
     */
    private $service;

    /**
     * @var TaskExecutorInterface
     */
    private $executor;

    /**
     * @var TimeProvider
     */
    private $timeProvider;

    public function __construct(
        TransactionLogService $service,
        TaskExecutorInterface $executor,
        TimeProvider $timeProvider
    ) {
        $this->service = $service;
        $this->executor = $executor;
        $this->timeProvider = $timeProvider;
    }

    /**
     * @param TransactionLogAwareInterface $job
     *
     * @return void
     *
     * @throws OrderNotFoundException
     * @throws Throwable
     */
    public function schedule(TransactionLogAwareInterface $job): void
    {
        $log = $this->service->createOrderLog($job, TransactionLog::QUEUED);
        $job->setTransactionLogId($log->getId());

        try {
            $this->executor->enqueue($job);
        } catch (Throwable $e) {
            $this->service->delete($log->getId());

            throw $e;
        }
    }

    /**
     * @param BusinessTaskInterface $job
     * @param callable $run
     *
     * @return void
     *
     * @throws Throwable
     */
    public function around(BusinessTaskInterface $job, callable $run): void
    {
        $this->service->deleteLogs(
            $this->timeProvider->getCurrentLocalTime()->modify('-1 month'),
            self::PURGE_BATCH_SIZE
        );

        $log = $this->startLog($job);

        try {
            $run();
        } catch (Throwable $e) {
            try {
                $this->markFailed($job, $log, $e);
            } finally {
                throw $e;
            }
        }

        $this->service->delete($log->getId());

        if (!$job instanceof TransactionLogAwareInterface) {
            $this->service->deleteFailedWithoutOrder($log->getEventCode());
        }
    }

    /**
     * @param BusinessTaskInterface $job
     *
     * @return TransactionLog
     *
     * @throws OrderNotFoundException
     */
    private function startLog(BusinessTaskInterface $job): TransactionLog
    {
        if (!$job instanceof TransactionLogAwareInterface) {
            return $this->service->createJobLog(
                (new ReflectionClass($job))->getShortName(),
                $this->timeProvider->getCurrentLocalTime()->getTimestamp(),
                TransactionLog::IN_PROGRESS
            );
        }

        $id = $job->getTransactionLogId();
        $log = $id !== null ? $this->service->findById($id) : null;

        if ($log === null) {
            $log = $this->service->createOrderLog($job, TransactionLog::IN_PROGRESS);
            $job->setTransactionLogId($log->getId());

            return $log;
        }

        $log->setQueueStatus(TransactionLog::IN_PROGRESS);
        $this->service->update($log);

        return $log;
    }

    /**
     * @param BusinessTaskInterface $job
     * @param TransactionLog $log
     * @param Throwable $error
     *
     * @return void
     */
    private function markFailed(BusinessTaskInterface $job, TransactionLog $log, Throwable $error): void
    {
        $log->setQueueStatus(TransactionLog::FAILED);
        $log->setReason($error->getMessage());

        if ($job instanceof TransactionLogAwareInterface) {
            $log->setFailureDescription(self::ORDER_FAILURE_DESCRIPTION);
        }

        $this->service->update($log);
    }
}
