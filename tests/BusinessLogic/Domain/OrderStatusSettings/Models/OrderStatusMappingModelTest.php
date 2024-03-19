<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\OrderStatusSettings\Models;

use SeQura\Core\BusinessLogic\Domain\Order\OrderStates;
use SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\Exceptions\EmptyOrderStatusMappingParameterException;
use SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\Exceptions\InvalidSeQuraOrderStatusException;
use SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\Models\OrderStatusMapping;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;

/**
 * Class OrderStatusMappingModelTest
 *
 * @package SeQura\Core\Tests\BusinessLogic\Domain\OrderStatusSettings\Models
 */
class OrderStatusMappingModelTest extends BaseTestCase
{
    public function testEmptySequraStatus(): void
    {
        $this->expectException(EmptyOrderStatusMappingParameterException::class);

        new OrderStatusMapping('', 'test');
    }

    public function testInvalidSequraStatus(): void
    {
        $this->expectException(InvalidSeQuraOrderStatusException::class);

        new OrderStatusMapping('test', 'test');
    }

    public function testSettersAndGetters(): void
    {
        $statusMapping = new OrderStatusMapping(OrderStates::STATE_APPROVED, 'Success');

        $statusMapping->setSequraStatus(OrderStates::STATE_CANCELLED);
        $statusMapping->setShopStatus('Failed');

        self::assertEquals(OrderStates::STATE_CANCELLED, $statusMapping->getSequraStatus());
        self::assertEquals('Failed', $statusMapping->getShopStatus());
    }
}
