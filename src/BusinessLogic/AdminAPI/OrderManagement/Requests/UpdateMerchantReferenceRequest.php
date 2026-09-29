<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\OrderManagement\Requests;

/**
 * Class UpdateMerchantReferenceRequest
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\OrderManagement\Requests
 */
class UpdateMerchantReferenceRequest
{
    /**
     * @var string
     */
    private $cartId;

    /**
     * @var string
     */
    private $shopOrderReference;

    /**
     * @param string $cartId Cart the order was financed for.
     * @param string $shopOrderReference Reference the shop knows the order by now.
     */
    public function __construct(string $cartId, string $shopOrderReference)
    {
        $this->cartId = $cartId;
        $this->shopOrderReference = $shopOrderReference;
    }

    /**
     * @return string
     */
    public function getCartId(): string
    {
        return $this->cartId;
    }

    /**
     * @return string
     */
    public function getShopOrderReference(): string
    {
        return $this->shopOrderReference;
    }
}
