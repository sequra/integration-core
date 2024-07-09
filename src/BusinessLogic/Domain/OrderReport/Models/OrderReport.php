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
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\OrderRequestDTO;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Tracking\Tracking;
use SeQura\Core\BusinessLogic\Domain\OrderReport\Exceptions\InvalidOrderDeliveryStateException;

/**
 * Class OrderReport
 *
 * @package SeQura\Core\BusinessLogic\Domain\OrderReport\Models\OrderRequest
 */
class OrderReport extends OrderRequestDTO
{
    /**
     * @var string String containing state of the order.
     */
    protected $state;

    /**
     * @var string|null String containing the time of sending the order report.
     */
    protected $sentAt;

    /**
     * @var MerchantReference Order id(s) used by the merchant.
     */
    protected $merchantReference;

    /**
     * @var Cart Fields describing the shopping cart.
     */
    protected $cart;

    /**
     * @var Tracking[]|null A list of trackings for the order.
     */
    protected $trackings;

    /**
     * @var Cart|null Fields describing the remaining cart.
     */
    protected $remainingCart;

    /**
     * @var DeliveryMethod Delivery method used on the purchase.
     */
    protected $deliveryMethod;

    /**
     * @var Address|null Fields describing the delivery address.
     */
    protected $deliveryAddress;

    /**
     * @var Address|null Fields describing the invoice address.
     */
    protected $invoiceAddress;

    /**
     * @var Customer Fields describing the customer.
     */
    protected $customer;

    /**
     * @param string $state
     * @param string|null $sentAt
     * @param MerchantReference $merchantReference
     * @param Cart $cart
     * @param Tracking[]|null $trackings
     * @param Cart|null $remainingCart
     * @param DeliveryMethod $deliveryMethod
     * @param Address|null $deliveryAddress
     * @param Address|null $invoiceAddress
     * @param Customer $customer
     *
     * @throws InvalidOrderDeliveryStateException
     */
    public function __construct(
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
    ) {
        if (!in_array($state, OrderDeliveryStates::toArray(), true)) {
            throw new InvalidOrderDeliveryStateException('Invalid order delivery state: ' . $state);
        }

        $this->state = $state;
        $this->sentAt = $sentAt;
        $this->merchantReference = $merchantReference;
        $this->cart = $cart;
        $this->trackings = $trackings;
        $this->remainingCart = $remainingCart;
        $this->deliveryMethod = $deliveryMethod;
        $this->deliveryAddress = $deliveryAddress;
        $this->invoiceAddress = $invoiceAddress;
        $this->customer = $customer;
    }

    /**
     * @return string
     */
    public function getState(): string
    {
        return $this->state;
    }

    /**
     * @return string|null
     */
    public function getSentAt(): ?string
    {
        return $this->sentAt;
    }

    /**
     * @return MerchantReference
     */
    public function getMerchantReference(): MerchantReference
    {
        return $this->merchantReference;
    }

    /**
     * @return Cart
     */
    public function getCart(): Cart
    {
        return $this->cart;
    }

    /**
     * @return Tracking[]|null
     */
    public function getTrackings(): ?array
    {
        return $this->trackings;
    }

    /**
     * @return Cart|null
     */
    public function getRemainingCart(): ?Cart
    {
        return $this->remainingCart;
    }

    /**
     * @return DeliveryMethod
     */
    public function getDeliveryMethod(): DeliveryMethod
    {
        return $this->deliveryMethod;
    }

    /**
     * @return Address|null
     */
    public function getDeliveryAddress(): ?Address
    {
        return $this->deliveryAddress;
    }

    /**
     * @return Address|null
     */
    public function getInvoiceAddress(): ?Address
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
     * Creates a new OrderReport instance from an array.
     *
     * @param array $data
     *
     * @return self
     *
     * @throws InvalidOrderDeliveryStateException
     * @throws InvalidCartItemsException
     * @throws InvalidDateException
     * @throws InvalidDurationException
     * @throws InvalidQuantityException
     * @throws InvalidServiceEndTimeException
     * @throws InvalidTimestampException
     */
    public static function fromArray(array $data): self
    {
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

        return new self(
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
     * @inheritDoc
     */
    public function toArray(): array
    {
        return $this->transformPropertiesToAnArray(get_object_vars($this));
    }
}
