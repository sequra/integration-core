<?php

namespace SeQura\Core\BusinessLogic\TransactionLog\Traits;

use SeQura\Core\BusinessLogic\DataAccess\TransactionLog\Entities\TransactionLog;

/**
 * Trait TransactionLogAwareTrait
 *
 * @package SeQura\Core\BusinessLogic\TransactionLog\Traits
 */
trait TransactionLogAwareTrait
{
    /**
     * @var TransactionLog
     */
    protected $transactionLog;

    /**
     * @return TransactionLog
     */
    public function getTransactionLog(): TransactionLog
    {
        return $this->transactionLog;
    }

    /**
     * @param TransactionLog $transactionLog
     */
    public function setTransactionLog(TransactionLog $transactionLog): void
    {
        $this->transactionLog = $transactionLog;
    }
}
