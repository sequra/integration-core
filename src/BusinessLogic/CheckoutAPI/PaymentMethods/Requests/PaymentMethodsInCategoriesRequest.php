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
     * @var string
     */
    protected $merchantId;

    /**
     * @param string $orderRef Reference of the solicited order.
     * @param string $merchantId Merchant the order was solicited for.
     */
    public function __construct(string $orderRef, string $merchantId)
    {
        $this->orderRef = $orderRef;
        $this->merchantId = $merchantId;
    }

    /**
     * @return string
     */
    public function getOrderRef(): string
    {
        return $this->orderRef;
    }

    /**
     * @return string
     */
    public function getMerchantId(): string
    {
        return $this->merchantId;
    }
}
