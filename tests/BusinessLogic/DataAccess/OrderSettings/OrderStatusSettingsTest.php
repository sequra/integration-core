<?php

namespace SeQura\Core\Tests\BusinessLogic\DataAccess\OrderSettings;

use SeQura\Core\BusinessLogic\DataAccess\OrderSettings\Entities\OrderStatusSettings;
use SeQura\Core\BusinessLogic\Domain\Order\OrderStates;
use SeQura\Core\BusinessLogic\Domain\OrderStatus\Models\OrderStatus;
use SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\Exceptions\EmptyOrderStatusMappingParameterException;
use SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\Exceptions\InvalidSeQuraOrderStatusException;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;

class OrderStatusSettingsTest extends BaseTestCase
{
    /**
     * @return void
     */
    public function testSettersAndGetters(): void
    {
        $orderStatusMapping = new OrderStatusSettings();

        $orderStatusMapping->setStoreId('store-123');
        $orderStatusMapping->setOrderStatusMappings([
            new OrderStatus(OrderStates::STATE_APPROVED, 'paid'),
            new OrderStatus(OrderStates::STATE_NEEDS_REVIEW, 'in_review'),
            new OrderStatus(OrderStates::STATE_CANCELLED, 'cancelled')
        ]);

        $this->assertEquals('store-123', $orderStatusMapping->getStoreId());
        $this->assertEquals([
            new OrderStatus(OrderStates::STATE_APPROVED, 'paid'),
            new OrderStatus(OrderStates::STATE_NEEDS_REVIEW, 'in_review'),
            new OrderStatus(OrderStates::STATE_CANCELLED, 'cancelled')
        ], $orderStatusMapping->getOrderStatusMappings());
    }

    /**
     * @throws InvalidSeQuraOrderStatusException
     * @throws EmptyOrderStatusMappingParameterException
     */
    public function testInflateAndToArray(): void
    {
        $data = [
            'storeId' => 'store-456',
            'orderStatusMappings' => [
                [
                    'sequraStatus' => OrderStates::STATE_APPROVED,
                    'shopStatus' => 'completed'
                ],
                [
                    'sequraStatus' => OrderStates::STATE_NEEDS_REVIEW,
                    'shopStatus' => 'pending'
                ],
                [
                    'sequraStatus' => OrderStates::STATE_CANCELLED,
                    'shopStatus' => 'failed'
                ]
            ],
        ];

        $orderStatusMapping = new OrderStatusSettings();
        $orderStatusMapping->inflate($data);

        $this->assertEquals($data['storeId'], $orderStatusMapping->toArray()['storeId']);
        $this->assertEquals($data['orderStatusMappings'], $orderStatusMapping->toArray()['orderStatusMappings']);
    }
}
