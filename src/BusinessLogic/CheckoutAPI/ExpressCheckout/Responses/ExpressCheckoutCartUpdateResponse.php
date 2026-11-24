<?php

namespace SeQura\Core\BusinessLogic\CheckoutAPI\ExpressCheckout\Responses;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;

/**
 * @package SeQura\Core\BusinessLogic\CheckoutAPI\ExpressCheckout\Responses
 */
class ExpressCheckoutCartUpdateResponse extends Response
{
    /**
     * @var array<string, mixed>
     */
    protected $cartData;

    /**
     * @param array<string, mixed> $cartData
     */
    public function __construct(array $cartData)
    {
        $this->cartData = $cartData;
    }

    /**
     * @return array<string, mixed>
     */
    public function getCartData(): array
    {
        return $this->cartData;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return $this->cartData;
    }
}
