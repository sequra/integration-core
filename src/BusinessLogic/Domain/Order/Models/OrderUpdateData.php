<?php

namespace SeQura\Core\BusinessLogic\Domain\Order\Models;

use Exception;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Address;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Cart;
use SeQura\Core\Infrastructure\Data\DataTransferObject;

/**
 * Class UpdateOrderDataRequest
 *
 * @package SeQura\Core\BusinessLogic\Domain\Order\Models
 */
class OrderUpdateData extends DataTransferObject
{
    /**
     * @var string
     */
    protected $orderShopReference;

    /**
     * @var Cart|null
     */
    protected $shippedCart;

    /**
     * @var Cart|null
     */
    protected $unshippedCart;

    /**
     * @var Address|null
     */
    protected $deliveryAddress;

    /**
     * @var Address|null
     */
    protected $invoiceAddress;

    /**
     * @param string $orderShopReference
     * @param Cart|null $shippedCart
     * @param Cart|null $unshippedCart
     * @param Address|null $deliveryAddress
     * @param Address|null $invoiceAddress
     */
    public function __construct(string $orderShopReference, ?Cart $shippedCart, ?Cart $unshippedCart, ?Address $deliveryAddress, ?Address $invoiceAddress)
    {
        $this->orderShopReference = $orderShopReference;
        $this->shippedCart = $shippedCart;
        $this->unshippedCart = $unshippedCart;
        $this->deliveryAddress = $deliveryAddress;
        $this->invoiceAddress = $invoiceAddress;
    }

    /**
     * @return string
     */
    public function getOrderShopReference(): string
    {
        return $this->orderShopReference;
    }

    /**
     * @param string $orderShopReference
     */
    public function setOrderShopReference(string $orderShopReference): void
    {
        $this->orderShopReference = $orderShopReference;
    }

    /**
     * @return Cart|null
     */
    public function getShippedCart(): ?Cart
    {
        return $this->shippedCart;
    }

    /**
     * @param Cart|null $shippedCart
     */
    public function setShippedCart(?Cart $shippedCart): void
    {
        $this->shippedCart = $shippedCart;
    }

    /**
     * @return Cart|null
     */
    public function getUnshippedCart(): ?Cart
    {
        return $this->unshippedCart;
    }

    /**
     * @param Cart|null $unshippedCart
     */
    public function setUnshippedCart(?Cart $unshippedCart): void
    {
        $this->unshippedCart = $unshippedCart;
    }

    /**
     * @return Address|null
     */
    public function getDeliveryAddress(): ?Address
    {
        return $this->deliveryAddress;
    }

    /**
     * @param Address|null $deliveryAddress
     */
    public function setDeliveryAddress(?Address $deliveryAddress): void
    {
        $this->deliveryAddress = $deliveryAddress;
    }

    /**
     * @return Address|null
     */
    public function getInvoiceAddress(): ?Address
    {
        return $this->invoiceAddress;
    }

    /**
     * @param Address|null $invoiceAddress
     */
    public function setInvoiceAddress(?Address $invoiceAddress): void
    {
        $this->invoiceAddress = $invoiceAddress;
    }

    /**
     * Create a OrderUpdateData instance from an array.
     *
     * @param array $data
     *
     * @return OrderUpdateData
     *
     * @throws Exception
     */
    public static function fromArray(array $data): self
    {
        $shippedCart = self::getDataValue($data, 'shipped_cart', null);
        $unshippedCart = self::getDataValue($data, 'unshipped_cart', null);
        $deliveryAddress = self::getDataValue($data, 'delivery_address', null);
        $invoiceAddress = self::getDataValue($data, 'invoice_address', null);

        return new self(
            self::getDataValue($data, 'order_shop_reference'),
            $shippedCart ? Cart::fromArray($shippedCart) : null,
            $unshippedCart ? Cart::fromArray($unshippedCart) : null,
            $deliveryAddress ? Address::fromArray($deliveryAddress) : null,
            $invoiceAddress ? Address::fromArray($invoiceAddress) : null
        );
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $data['order_shop_reference'] = $this->orderShopReference;
        $data['shipped_cart'] = $this->shippedCart ? $this->shippedCart->toArray() : null;
        $data['unshipped_cart'] = $this->unshippedCart ? $this->unshippedCart->toArray() : null;
        $data['delivery_address'] = $this->deliveryAddress ? $this->deliveryAddress->toArray() : null;
        $data['invoice_address'] = $this->invoiceAddress ? $this->invoiceAddress->toArray() : null;

        return $data;
    }
}
