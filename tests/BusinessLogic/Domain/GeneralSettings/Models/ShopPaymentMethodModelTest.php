<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\GeneralSettings\Models;

use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Exceptions\EmptyShopPaymentMethodParameterException;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Models\ShopPaymentMethod;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;

/**
 * Class ShopPaymentMethodModelTest
 *
 * @package SeQura\Core\Tests\BusinessLogic\Domain\GeneralSettings\Models
 */
class ShopPaymentMethodModelTest extends BaseTestCase
{
    public function testEmptyShopPaymentMethodCode(): void
    {
        $this->expectException(EmptyShopPaymentMethodParameterException::class);

        new ShopPaymentMethod('', 'test');
    }

    public function testEmptyShopPaymentMethodName(): void
    {
        $this->expectException(EmptyShopPaymentMethodParameterException::class);

        new ShopPaymentMethod('test', '');
    }

    /**
     * @return void
     */
    public function testSettersAndGetters(): void
    {
        $paymentMethod = new ShopPaymentMethod('1', 'Test name 1');
        $paymentMethod->setCode('2');
        $paymentMethod->setName('Tester 2');

        self::assertEquals('2', $paymentMethod->getCode());
        self::assertEquals('Tester 2', $paymentMethod->getName());
    }
}
