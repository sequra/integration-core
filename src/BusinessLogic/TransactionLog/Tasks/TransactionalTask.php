<?php

namespace SeQura\Core\BusinessLogic\TransactionLog\Tasks;

use SeQura\Core\BusinessLogic\TransactionLog\Contracts\TransactionLogAwareInterface;
use SeQura\Core\BusinessLogic\TransactionLog\Traits\TransactionLogAwareTrait;
use SeQura\Core\Infrastructure\TaskExecution\Task;

/**
 * Class TransactionalTask
 *
 * @package SeQura\Core\BusinessLogic\TransactionLog\Tasks
 */
abstract class TransactionalTask extends Task implements TransactionLogAwareInterface
{
    use TransactionLogAwareTrait;
}
