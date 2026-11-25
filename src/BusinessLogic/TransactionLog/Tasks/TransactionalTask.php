<?php

namespace SeQura\Core\BusinessLogic\TransactionLog\Tasks;

use SeQura\Core\BusinessLogic\TransactionLog\Contracts\TransactionLogAwareInterface;
use SeQura\Core\BusinessLogic\TransactionLog\Traits\TransactionLogAwareTrait;

/**
 * Class TransactionalTask
 *
 * @package SeQura\Core\BusinessLogic\TransactionLog\Tasks
 */
abstract class TransactionalTask implements TransactionLogAwareInterface
{
    use TransactionLogAwareTrait;
}
