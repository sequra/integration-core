<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use SeQura\Core\BusinessLogic\Domain\Integration\Order\OrderCreationInterface;

class MockOrderCreation implements OrderCreationInterface
{
    /**
     * @var string
     */
    private $shopOrderReference = '';

    public function getShopOrderReference(string $idReference): string
    {
        return $this->shopOrderReference;
    }

    public function setShopOrderReference(string $shopOrderReference): void
    {
        $this->shopOrderReference = $shopOrderReference;
    }
}
