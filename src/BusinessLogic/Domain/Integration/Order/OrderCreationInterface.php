<?php

namespace SeQura\Core\BusinessLogic\Domain\Integration\Order;

/**
 * Interface OrderCreationInterface
 *
 * @package SeQura\Core\BusinessLogic\Domain\Integration\Order
 */
interface OrderCreationInterface
{
    /**
     * Creates shop order and returns shop order reference.
     *
     * @param string $cartId
     *
     * @return string
     */
    public function createOrder(string $cartId): string;
}
