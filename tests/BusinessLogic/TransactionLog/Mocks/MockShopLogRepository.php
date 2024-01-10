<?php

namespace SeQura\Core\Tests\BusinessLogic\TransactionLog\Mocks;

use SeQura\Core\BusinessLogic\TransactionLog\RepositoryContracts\ShopLogRepositoryInterface;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MemoryRepositoryWithConditionalDelete;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockConditionalDeleteTrait;

/**
 * Class MockShopLogRepository
 *
 * @package SeQura\Core\Tests\BusinessLogic\TransactionLog\Mocks
 */
class MockShopLogRepository extends MemoryRepositoryWithConditionalDelete implements ShopLogRepositoryInterface
{
    use MockConditionalDeleteTrait;

    public const THIS_CLASS_NAME = __CLASS__;
}
