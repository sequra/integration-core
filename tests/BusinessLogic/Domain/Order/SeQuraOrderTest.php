<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\Order;

use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\MerchantReference;
use SeQura\Core\BusinessLogic\Domain\Order\Models\SeQuraOrder;
use SeQura\Core\Infrastructure\ORM\QueryFilter\Operators;
use SeQura\Core\Infrastructure\ORM\QueryFilter\QueryFilter;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\ORM\TestRepositoryRegistry;

class SeQuraOrderTest extends BaseTestCase
{
    /**
     * @return void
     */
    public function testSettersAndGetters()
    {
        $seQuraOrder = new SeQuraOrder();
        $seQuraOrder->setReference('sequra-ref-1234');
        $seQuraOrder->setCartId('5678');
        $seQuraOrder->setOrderRef1('ZXCV1234');
        $merchantReference = MerchantReference::fromArray([
            "order_ref_1" => "ZXCV1234",
            "order_ref_2" => "0080-1234-4343-5353",
        ]);

        $seQuraOrder->setMerchantReference($merchantReference);
        $seQuraOrder->setState('approved');

        self::assertEquals('sequra-ref-1234', $seQuraOrder->getReference());
        self::assertEquals('5678', $seQuraOrder->getCartId());
        self::assertEquals('ZXCV1234', $seQuraOrder->getOrderRef1());
        self::assertEquals('ZXCV1234', $seQuraOrder->getMerchantReference()->getOrderRef1());
        self::assertEquals('0080-1234-4343-5353', $seQuraOrder->getMerchantReference()->getOrderRef2());
        self::assertEquals('approved', $seQuraOrder->getState());
    }

    /**
     * @return void
     */
    public function testOrderPersisting()
    {
        $order = file_get_contents(
            __DIR__ . '/../../Common/MockObjects/SeQuraOrder.json'
        );
        $array = json_decode($order, true);
        $seQuraOrder = SeQuraOrder::fromArray($array['order']);
        $seQuraOrder->setReference('sequra-ref-1234');
        $seQuraOrder->setCartId('5678');
        $seQuraOrder->setOrderRef1('ZXCV1234');
        $seQuraOrder->setState('approved');

        $repository = TestRepositoryRegistry::getRepository(SeQuraOrder::getClassName());
        $repository->save($seQuraOrder);

        $filter = new QueryFilter();
        $filter->where('reference', Operators::EQUALS, $seQuraOrder->getReference());
        /** @var SeQuraOrder $storedOrder */
        $storedOrder = $repository->selectOne($filter);

        self::assertEquals($storedOrder->getReference(), $seQuraOrder->getReference());
        self::assertEquals($storedOrder->getCartId(), $seQuraOrder->getCartId());
        self::assertEquals($storedOrder->getOrderRef1(), $seQuraOrder->getOrderRef1());
        self::assertEquals($storedOrder->getMerchantReference()->getOrderRef1(), $seQuraOrder->getMerchantReference()->getOrderRef1());
        self::assertEquals($storedOrder->getMerchantReference()->getOrderRef2(), $seQuraOrder->getMerchantReference()->getOrderRef2());
        self::assertEquals($storedOrder->getState(), $seQuraOrder->getState());
    }
}
