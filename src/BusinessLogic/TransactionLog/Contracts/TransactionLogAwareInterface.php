<?php

namespace SeQura\Core\BusinessLogic\TransactionLog\Contracts;

use SeQura\Core\BusinessLogic\DataAccess\TransactionLog\Entities\TransactionLog;
use SeQura\Core\BusinessLogic\TransactionLog\Models\TransactionData;

/**
 * Interface TransactionLogAwareTaskInterface
 *
 * @package SeQura\Core\BusinessLogic\TransactionLog\Contracts
 */
interface TransactionLogAwareInterface
{
    /**
     * Provides transaction log.
     *
     * @return TransactionLog
     */
    public function getTransactionLog(): TransactionLog;

    /**
     * Sets transaction log.
     *
     * @param TransactionLog $transactionLog
     */
    public function setTransactionLog(TransactionLog $transactionLog);

    /**
     * Gets the store id for which the transactional task is created
     *
     * @return string
     */
    public function getStoreId(): string;

    /**
     * Gets the transaction data for which the transactional task is created
     *
     * @return TransactionData
     */
    public function getTransactionData(): TransactionData;
}
