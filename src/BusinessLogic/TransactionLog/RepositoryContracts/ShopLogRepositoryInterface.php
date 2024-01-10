<?php

namespace SeQura\Core\BusinessLogic\TransactionLog\RepositoryContracts;

use SeQura\Core\Infrastructure\ORM\Interfaces\ConditionallyDeletes;
use SeQura\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;

/**
 * Interface ShopLogRepositoryInterface
 *
 * @package SeQura\Core\BusinessLogic\TransactionLog\RepositoryContracts
 */
interface ShopLogRepositoryInterface extends ConditionallyDeletes, RepositoryInterface
{
}
