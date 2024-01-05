<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\OrderStatus\Models;

use SeQura\Core\BusinessLogic\Domain\OrderStatus\Exceptions\EmptyOrderStatusParameterException;
use SeQura\Core\BusinessLogic\Domain\OrderStatus\Models\OrderStatus;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;

/**
 * Class OrderStatusModelTest
 *
 * @package SeQura\Core\Tests\BusinessLogic\Domain\OrderStatus\Models
 */
class OrderStatusModelTest extends BaseTestCase
{
    public function testEmptyOrderStatusId(): void
    {
        $this->expectException(EmptyOrderStatusParameterException::class);

        new OrderStatus('', 'test');
    }

    public function testEmptyOrderStatusName(): void
    {
        $this->expectException(EmptyOrderStatusParameterException::class);

        new OrderStatus('test', '');
    }

    /**
     * @return void
     */
    public function testSettersAndGetters(): void
    {
        $orderStatus = new OrderStatus('1', 'Test name 1');
        $orderStatus->setId('2');
        $orderStatus->setName('Tester 2');

        self::assertEquals('2', $orderStatus->getId());
        self::assertEquals('Tester 2', $orderStatus->getName());
    }
}
