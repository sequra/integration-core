<?php

namespace SeQura\Core\BusinessLogic\CheckoutAPI\PaymentMethods\Requests;

/**
 * Class PaymentMethodsInCategoriesRequest.
 *
 * @package SeQura\Core\BusinessLogic\CheckoutAPI\PaymentMethods\Requests
 */
class PaymentMethodsInCategoriesRequest
{
    /**
     * @var string
     */
    protected $orderRef;

    /**
     * @param string $orderRef Reference of the solicited order.
     */
    public function __construct(string $orderRef)
    {
        $this->orderRef = $orderRef;
    }

    /**
     * @return string
     */
    public function getOrderRef(): string
    {
        return $this->orderRef;
    }
}
