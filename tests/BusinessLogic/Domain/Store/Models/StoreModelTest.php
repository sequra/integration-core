<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\Store\Models;

use SeQura\Core\BusinessLogic\Domain\Stores\Exceptions\EmptyStoreParameterException;
use SeQura\Core\BusinessLogic\Domain\Stores\Models\Store;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;

/**
 * Class StoreModelTest
 *
 * @package SeQura\Core\Tests\BusinessLogic\Domain\Store\Models
 */
class StoreModelTest extends BaseTestCase
{
    public function testEmptyStoreId(): void
    {
        $this->expectException(EmptyStoreParameterException::class);

        new Store('', 'test');
    }

    public function testEmptyStoreName(): void
    {
        $this->expectException(EmptyStoreParameterException::class);

        new Store('test', '');
    }

    public function testSettersAndGetters(): void
    {
        $store = new Store('3', 'Test store 3');

        $store->setStoreId('1');
        $store->setStoreName('Test store 1');

        self::assertEquals('1', $store->getStoreId());
        self::assertEquals('Test store 1', $store->getStoreName());
    }
}
