<?php

namespace SeQura\Core\BusinessLogic\Domain\Integration\ExpressCheckout;

use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Models\CartSummary;
use SeQura\Core\BusinessLogic\Domain\Order\Builders\CreateOrderRequestBuilder;

/**
 * @package SeQura\Core\BusinessLogic\Domain\Integration\ExpressCheckout
 */
interface ExpressCheckoutCartProviderInterface
{
    /**
     * @param string $cartId
     *
     * @return CartSummary
     */
    public function getCartSummary(string $cartId): CartSummary;

    /**
     * @param string $cartId
     *
     * @return CreateOrderRequestBuilder A builder whose cart reference is $cartId.
     */
    public function getCreateOrderRequestBuilder(string $cartId): CreateOrderRequestBuilder;
}
