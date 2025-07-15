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
     * Returns shop order reference.
     *
     * @param string $idReference
     *
     * @return string
     */
    public function getShopOrderReference(string $idReference): string;
}
