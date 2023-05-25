<?php

namespace SeQura\Core\Tests\BusinessLogic\DataAccess\OrderSettings;

use SeQura\Core\BusinessLogic\DataAccess\OrderSettings\Entities\OrderStatusMapping;
use SeQura\Core\BusinessLogic\Domain\Order\OrderStates;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;

class OrderStatusMappingTest extends BaseTestCase
{
    /**
     * @return void
     */
    public function testSettersAndGetters()
    {
        $orderStatusMapping = new OrderStatusMapping();

        $orderStatusMapping->setStoreId('store-123');
        $orderStatusMapping->setOrderStatusMappingSettings([
            OrderStates::STATE_APPROVED => 'paid',
            OrderStates::STATE_NEEDS_REVIEW => 'in_review',
            OrderStates::STATE_CANCELLED => 'cancelled',
        ]);

        $this->assertEquals('store-123', $orderStatusMapping->getStoreId());
        $this->assertEquals([
            OrderStates::STATE_APPROVED => 'paid',
            OrderStates::STATE_NEEDS_REVIEW => 'in_review',
            OrderStates::STATE_CANCELLED => 'cancelled',
        ], $orderStatusMapping->getOrderStatusMappingSettings());
    }

    /**
     * @return void
     */
    public function testInflateAndToArray()
    {
        $data = [
            'storeId' => 'store-456',
            'orderStatusMapping' => [
                OrderStates::STATE_APPROVED => 'completed',
                OrderStates::STATE_NEEDS_REVIEW => 'pending',
                OrderStates::STATE_CANCELLED => 'failed',
            ],
        ];

        $orderStatusMapping = new OrderStatusMapping();
        $orderStatusMapping->inflate($data);

        $this->assertEquals($data['storeId'], $orderStatusMapping->toArray()['storeId']);
        $this->assertEquals($data['orderStatusMapping'], $orderStatusMapping->toArray()['orderStatusMapping']);
    }
}
