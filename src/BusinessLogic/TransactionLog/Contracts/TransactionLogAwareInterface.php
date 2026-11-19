<?php

namespace SeQura\Core\BusinessLogic\TransactionLog\Contracts;

use SeQura\Core\BusinessLogic\Domain\Integration\TaskExecution\BusinessTaskInterface;
use SeQura\Core\BusinessLogic\TransactionLog\Models\TransactionData;

/**
 * @package SeQura\Core\BusinessLogic\TransactionLog\Contracts
 */
interface TransactionLogAwareInterface extends BusinessTaskInterface
{
    /**
     * @return int|null
     */
    public function getTransactionLogId(): ?int;

    /**
     * @param int $id
     *
     * @return void
     */
    public function setTransactionLogId(int $id): void;

    /**
     * @return TransactionData
     */
    public function getTransactionData(): TransactionData;
}
