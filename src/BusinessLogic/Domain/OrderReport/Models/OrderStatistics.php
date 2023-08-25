<?php

namespace SeQura\Core\BusinessLogic\Domain\OrderReport\Models;

use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidCartItemsException;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidDateException;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidDurationException;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidQuantityException;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidServiceEndTimeException;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidTimestampException;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Address;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Cart;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Customer;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\DeliveryMethod;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\MerchantReference;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Tracking\Tracking;
use SeQura\Core\BusinessLogic\Domain\OrderReport\Exceptions\InvalidOrderDeliveryStateException;

/**
 * Class OrderStatistics
 *
 * @package SeQura\Core\BusinessLogic\Domain\OrderReport\Models
 */
class OrderStatistics extends OrderReport
{
    /**
     * @var string Filed representing the date of order completion.
     */
    private $completedAt;

    /**
     * @var string Field representing the order currency.
     */
    private $currency;

    /**
     * @param $completedAt
     * @param $currency
     * @param string $state
     * @param MerchantReference $merchantReference
     * @param Cart $cart
     * @param DeliveryMethod $deliveryMethod
     * @param Customer $customer
     * @param string|null $sentAt
     * @param array|null $trackings
     * @param Cart|null $remainingCart
     * @param Address|null $deliveryAddress
     * @param Address|null $invoiceAddress
     *
     * @throws InvalidOrderDeliveryStateException
     */
    public function __construct(
        $completedAt,
        $currency,
        string $state,
        MerchantReference $merchantReference,
        Cart $cart,
        DeliveryMethod $deliveryMethod,
        Customer $customer,
        ?string $sentAt = null,
        ?array $trackings = null,
        ?Cart $remainingCart = null,
        ?Address $deliveryAddress = null,
        ?Address $invoiceAddress = null
    )
    {
        parent::__construct(
            $state,
            $merchantReference,
            $cart,
            $deliveryMethod,
            $customer,
            $sentAt,
            $trackings,
            $remainingCart,
            $deliveryAddress,
            $invoiceAddress
        );

        $this->completedAt = $completedAt;
        $this->currency = $currency;
    }

    /**
     * Creates a new OrderStatistics instance from an array.
     *
     * @param array $data
     *
     * @return OrderReport
     *
     * @throws InvalidOrderDeliveryStateException
     * @throws InvalidCartItemsException
     * @throws InvalidDateException
     * @throws InvalidDurationException
     * @throws InvalidQuantityException
     * @throws InvalidServiceEndTimeException
     * @throws InvalidTimestampException
     */
    public static function fromArray(array $data): OrderReport
    {
        $completedAt = self::getDataValue($data, 'completed_at');
        $currency = self::getDataValue($data, 'currency');
        $state = self::getDataValue($data, 'state');
        $merchantReference = MerchantReference::fromArray(self::getDataValue($data, 'merchant_reference', []));
        $cart = Cart::fromArray(self::getDataValue($data, 'cart', []));
        $deliveryMethod = DeliveryMethod::fromArray(self::getDataValue($data, 'delivery_method', []));
        $customer = Customer::fromArray(self::getDataValue($data, 'customer', []));
        $sentAt = self::getDataValue($data, 'sent_at', null);

        $deliveryAddress = self::getDataValue($data, 'delivery_address', null);
        if ($deliveryAddress !== null) {
            $deliveryAddress = Address::fromArray($deliveryAddress);
        }

        $invoiceAddress = self::getDataValue($data, 'invoice_address', null);
        if ($invoiceAddress !== null) {
            $invoiceAddress = Address::fromArray($invoiceAddress);
        }

        $remainingCart = self::getDataValue($data, 'remaining_cart', null);
        if ($remainingCart !== null) {
            $remainingCart = Cart::fromArray($remainingCart);
        }

        $trackings = self::getDataValue($data, 'trackings', null);
        if ($trackings !== null) {
            $trackings = array_map(static function ($tracking) {
                return Tracking::fromArray($tracking);
            }, $trackings);
        }

        return new OrderStatistics(
            $completedAt,
            $currency,
            $state,
            $merchantReference,
            $cart,
            $deliveryMethod,
            $customer,
            $sentAt,
            $trackings,
            $remainingCart,
            $deliveryAddress,
            $invoiceAddress
        );
    }

    /**
     * @return string
     */
    public function getCompletedAt(): string
    {
        return $this->completedAt;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), $this->transformPropertiesToAnArray(get_object_vars($this)));
    }
}
