<?php

namespace SeQura\Core\BusinessLogic\TransactionLog\Traits;

/**
 * Trait TransactionLogAwareTrait
 *
 * @package SeQura\Core\BusinessLogic\TransactionLog\Traits
 */
trait TransactionLogAwareTrait
{
    /**
     * @var int|null
     */
    protected $transactionLogId;

    /**
     * @return int|null
     */
    public function getTransactionLogId(): ?int
    {
        return $this->transactionLogId;
    }

    /**
     * @param int $id
     *
     * @return void
     */
    public function setTransactionLogId(int $id): void
    {
        $this->transactionLogId = $id;
    }
}
