<?php

namespace SeQura\Core\Tests\BusinessLogic\TransactionLog\Mocks;

use DateTime;
use SeQura\Core\BusinessLogic\DataAccess\TransactionLog\Entities\TransactionLog;
use SeQura\Core\BusinessLogic\TransactionLog\RepositoryContracts\TransactionLogRepositoryInterface;

/**
 * Class MockTransactionLogRepository
 *
 * @package SeQura\Core\Tests\BusinessLogic\TransactionLog\Mocks
 */
class MockTransactionLogRepository implements TransactionLogRepositoryInterface
{
    /**
     * @var TransactionLog
     */
    private $transactionLog;

    /**
     * @inheritDoc
     */
    public function getTransactionLog(string $merchantReference): ?TransactionLog
    {
        return $this->transactionLog;
    }

    /**
     * @inheritDoc
     */
    public function setTransactionLog(TransactionLog $transactionLog): void
    {
        $this->transactionLog = $transactionLog;
    }

    /**
     * @inheritDoc
     */
    public function getItemByExecutionId(int $executionId): ?TransactionLog
    {
        return $this->transactionLog;
    }

    public function find(int $limit, int $offset, ?DateTime $disconnectTime = null): array
    {
        return [];
    }

    public function count(?DateTime $disconnectTime = null): int
    {
        return 1;
    }

    public function findByMerchantReference(string $merchantReference): ?TransactionLog
    {
        return null;
    }

    public function updateTransactionLog(TransactionLog $transactionLog): void
    {
    }

    public function logsExist(DateTime $beforeDate): bool
    {
        return false;
    }

    public function deleteLogs(DateTime $beforeDate, int $limit): void
    {
    }

    public function deleteTransactionLogById(int $id): void
    {
    }
}
