<?php

namespace SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest;

use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Tracking\Tracking;

/**
 * Class BaseOrderRequest
 *
 * @package SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest
 */
abstract class BaseOrderRequest extends OrderRequestDTO
{
    /**
     * @var Merchant Fields describing the merchant and the store integration.
     */
    protected $merchant;

    /**
     * @var MerchantReference|null Order id(s) used by the merchant.
     */
    protected $merchantReference;

    /**
     * @var Tracking[]|null A list of trackings for the order.
     */
    protected $trackings;

    /**
     * @var DeliveryMethod Delivery method used on the purchase.
     */
    protected $deliveryMethod;

    /**
     * @var Address Fields describing the delivery address.
     */
    protected $deliveryAddress;

    /**
     * @var Address Fields describing the invoice address.
     */
    protected $invoiceAddress;

    /**
     * @var Customer Fields describing the customer.
     */
    protected $customer;

    /**
     * @var Platform Fields describing the store platform.
     */
    protected $platform;

    /**
     * @return Merchant
     */
    public function getMerchant(): Merchant
    {
        return $this->merchant;
    }

    /**
     * @return MerchantReference|null
     */
    public function getMerchantReference(): ?MerchantReference
    {
        return $this->merchantReference;
    }

    /**
     * @return array|Tracking[]|null
     */
    public function getTrackings(): ?array
    {
        return $this->trackings;
    }

    /**
     * @return DeliveryMethod
     */
    public function getDeliveryMethod(): DeliveryMethod
    {
        return $this->deliveryMethod;
    }

    /**
     * @return Address
     */
    public function getDeliveryAddress(): Address
    {
        return $this->deliveryAddress;
    }

    /**
     * @return Address
     */
    public function getInvoiceAddress(): Address
    {
        return $this->invoiceAddress;
    }

    /**
     * @return Customer
     */
    public function getCustomer(): Customer
    {
        return $this->customer;
    }

    /**
     * @return Platform
     */
    public function getPlatform(): Platform
    {
        return $this->platform;
    }
}
