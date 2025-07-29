<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use SeQura\Core\BusinessLogic\Domain\Integration\Order\OrderCreationInterface;

/**
 * class MockOrderCreation
 *
 * @package SeQura\Core\Tests\BusinessLogic\Common\MockComponents
 */
class MockOrderCreation implements OrderCreationInterface
{
    /**
     * @var string
     */
    private $shopOrderReference = '';

    /**
     * @inheritDoc
     */
    public function createOrder(string $cartId): string
    {
        return $this->shopOrderReference;
    }

    /**
     * @param string $shopOrderReference
     * @return void
     */
    public function setShopOrderReference(string $shopOrderReference): void
    {
        $this->shopOrderReference = $shopOrderReference;
    }
}
