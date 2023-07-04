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
    private $unshippedCart;

    /**
     * @var Cart Fields describing the shipped cart.
     */
    private $shippedCart;

    /**
     * @param Merchant $merchant
     * @param Cart $unshippedCart
     * @param Cart $shippedCart
     * @param DeliveryMethod $deliveryMethod
     * @param Customer $customer
     * @param Platform $platform
     * @param Address $deliveryAddress
     * @param Address $invoiceAddress
     * @param MerchantReference|null $merchantReference
     * @param array|null $trackings
     */
    public function __construct(
        Merchant $merchant,
        Cart $unshippedCart,
        Cart $shippedCart,
        DeliveryMethod $deliveryMethod,
        Customer $customer,
        Platform $platform,
        Address $deliveryAddress,
        Address $invoiceAddress,
        MerchantReference $merchantReference = null,
        array $trackings = null
    )
    {
        $this->merchant = $merchant;
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
        $merchant = Merchant::fromArray(self::getDataValue($data, 'merchant', []));
        $unshippedCart = Cart::fromArray(self::getDataValue($data, 'unshipped_cart', []));
        $shippedCart = Cart::fromArray(self::getDataValue($data, 'shipped_cart', []));
        $deliveryMethod = DeliveryMethod::fromArray(self::getDataValue($data, 'delivery_method', []));
        $customer = Customer::fromArray(self::getDataValue($data, 'customer', []));
        $platform = Platform::fromArray(self::getDataValue($data, 'platform', []));
        $deliveryAddress = Address::fromArray(self::getDataValue($data, 'delivery_address', []));
        $invoiceAddress = Address::fromArray(self::getDataValue($data, 'invoice_address', []));

        $merchantReference = self::getDataValue($data, 'merchant_reference', null);
        if ($merchantReference !== null) {
            $merchantReference = MerchantReference::fromArray($merchantReference);
        }

        $trackings = self::getDataValue($data, 'trackings', null);
        if ($trackings !== null) {
            $trackings = array_map(static function ($tracking) {
                return Tracking::fromArray($tracking);
            }, $trackings);
        }

        return new self(
            $merchant,
            $unshippedCart,
            $shippedCart,
            $deliveryMethod,
            $customer,
            $platform,
            $deliveryAddress,
            $invoiceAddress,
            $merchantReference,
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
}
