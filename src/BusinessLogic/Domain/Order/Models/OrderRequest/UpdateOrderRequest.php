<?php

namespace SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest;

use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidCartItemsException;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidDateException;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidDurationException;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidQuantityException;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidServiceEndTimeException;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidTimestampException;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidUrlException;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Tracking\Tracking;

/**
 * Class CreateOrderRequest
 *
 * @package SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest
 */
class UpdateOrderRequest extends BaseOrderRequest
{
    /**
     * @var Cart Fields describing the unshipped cart.
     */
    protected $unshippedCart;

    /**
     * @var Cart Fields describing the shipped cart.
     */
    protected $shippedCart;

    /**
     * @param Merchant $merchant
     * @param MerchantReference $merchantReference
     * @param Platform $platform
     * @param Cart $unshippedCart
     * @param Cart $shippedCart
     * @param DeliveryMethod|null $deliveryMethod
     * @param Customer|null $customer
     * @param Address|null $deliveryAddress
     * @param Address|null $invoiceAddress
     * @param Tracking[]|null $trackings
     *
     * @throws InvalidUrlException
     */
    public function __construct(
        Merchant $merchant,
        MerchantReference $merchantReference,
        Platform $platform,
        Cart $unshippedCart,
        Cart $shippedCart,
        DeliveryMethod $deliveryMethod = null,
        Customer $customer = null,
        Address $deliveryAddress = null,
        Address $invoiceAddress = null,
        array $trackings = null
    ) {
        $merchantId = $merchant->getId();

        $this->merchant = new Merchant($merchantId);
        $this->unshippedCart = $unshippedCart;
        $this->shippedCart = $shippedCart;
        $this->deliveryMethod = $deliveryMethod;
        $this->customer = $customer;
        $this->platform = $platform;
        $this->deliveryAddress = $deliveryAddress;
        $this->invoiceAddress = $invoiceAddress;
        $this->merchantReference = $merchantReference;
        $this->trackings = $trackings;
    }

    /**
     * Create a UpdateOrderRequest instance from an array.
     *
     * @param array $data
     *
     * @return UpdateOrderRequest
     * @throws InvalidCartItemsException
     * @throws InvalidDateException
     * @throws InvalidDurationException
     * @throws InvalidQuantityException
     * @throws InvalidServiceEndTimeException
     * @throws InvalidUrlException
     * @throws InvalidTimestampException
     */
    public static function fromArray(array $data): self
    {
        $merchantData = Merchant::fromArray(self::getDataValue($data, 'merchant', []));

        $merchant = Merchant::fromArray(['id' => $merchantData->getId()]);
        $unshippedCart = Cart::fromArray(self::getDataValue($data, 'unshipped_cart', []));
        $shippedCart = Cart::fromArray(self::getDataValue($data, 'shipped_cart', []));
        $platform = Platform::fromArray(self::getDataValue($data, 'platform', []));
        $merchantReference = MerchantReference::fromArray(self::getDataValue($data, 'merchant_reference', []));

        $deliveryMethod = self::getDataValue($data, 'delivery_method', null);
        if ($deliveryMethod !== null) {
            $deliveryMethod = DeliveryMethod::fromArray($deliveryMethod);
        }

        $customer = self::getDataValue($data, 'customer', null);
        if ($customer !== null) {
            $customer = Customer::fromArray($customer);
        }

        $deliveryAddress = self::getDataValue($data, 'delivery_address', null);
        if ($deliveryAddress !== null) {
            $deliveryAddress = Address::fromArray($deliveryAddress);
        }

        $invoiceAddress = self::getDataValue($data, 'invoice_address', null);
        if ($invoiceAddress !== null) {
            $invoiceAddress = Address::fromArray($invoiceAddress);
        }

        $trackings = self::getDataValue($data, 'trackings', null);
        if ($trackings !== null) {
            $trackings = array_map(static function ($tracking) {
                return Tracking::fromArray($tracking);
            }, $trackings);
        }

        return new self(
            $merchant,
            $merchantReference,
            $platform,
            $unshippedCart,
            $shippedCart,
            $deliveryMethod,
            $customer,
            $deliveryAddress,
            $invoiceAddress,
            $trackings
        );
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return $this->transformPropertiesToAnArray(get_object_vars($this));
    }

    /**
     * @return Cart
     */
    public function getUnshippedCart(): Cart
    {
        return $this->unshippedCart;
    }

    /**
     * @return Cart
     */
    public function getShippedCart(): Cart
    {
        return $this->shippedCart;
    }
}
