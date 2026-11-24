<?php

namespace SeQura\Core\BusinessLogic\Domain\Integration\ExpressCheckout;

use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Exceptions\InvalidShippingMethodReferenceException;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Models\CartChange;

/**
 * @package SeQura\Core\BusinessLogic\Domain\Integration\ExpressCheckout
 */
interface ExpressCheckoutCartUpdaterInterface
{
    /**
     * @param string $cartId
     *
     * @return bool False when the cart no longer exists or has already become a shop order.
     */
    public function isCartOpen(string $cartId): bool;

    /**
     * @param string $cartId
     * @param CartChange $change
     *
     * @return bool False when the changed cart has no usable shipping rate or an address the shop would refuse.
     *
     * @throws InvalidShippingMethodReferenceException When the cart does not offer the shipping method reference.
     */
    public function applyCartChange(string $cartId, CartChange $change): bool;
}
