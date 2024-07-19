<?php

namespace SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest;

use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidCartItemsException;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidDateException;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidDurationException;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidGuiLayoutValueException;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidQuantityException;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidServiceEndTimeException;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidTimestampException;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidUrlException;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Tracking\Tracking;
use SeQura\Core\BusinessLogic\Domain\Order\Models\SeQuraOrder;

/**
 * Class CreateOrderRequest
 *
 * @package SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest
 */
class CreateOrderRequest extends BaseOrderRequest
{
    /**
     * @var string String containing state of the order.
     */
    protected $state;

    /**
     * @var Cart Fields describing the shopping cart.
     */
    protected $cart;

    /**
     * @var Gui Fields describing the medium for which the response will be generated.
     */
    protected $gui;

    /**
     * @param string $state
     * @param Merchant $merchant
     * @param Cart $cart
     * @param DeliveryMethod $deliveryMethod
     * @param Customer $customer
     * @param Platform $platform
     * @param Address $deliveryAddress
     * @param Address $invoiceAddress
     * @param Gui $gui
     * @param MerchantReference|null $merchantReference
     * @param array|null $trackings
     */
    public function __construct(
        string $state,
        Merchant $merchant,
        Cart $cart,
        DeliveryMethod $deliveryMethod,
        Customer $customer,
        Platform $platform,
        Address $deliveryAddress,
        Address $invoiceAddress,
        Gui $gui,
        MerchantReference $merchantReference = null,
        array $trackings = null
    ) {
        $this->state = $state;
        $this->merchant = $merchant;
        $this->cart = $cart;
        $this->deliveryMethod = $deliveryMethod;
        $this->customer = $customer;
        $this->platform = $platform;
        $this->deliveryAddress = $deliveryAddress;
        $this->invoiceAddress = $invoiceAddress;
        $this->gui = $gui;
        $this->merchantReference = $merchantReference;
        $this->trackings = $trackings;
    }

    /**
     * Create a CreateOrderRequest instance from an array.
     *
     * @param array $data
     *
     * @return CreateOrderRequest
     * @throws InvalidCartItemsException
     * @throws InvalidGuiLayoutValueException
     * @throws InvalidTimestampException
     * @throws InvalidUrlException
     * @throws InvalidDateException
     * @throws InvalidDurationException
     * @throws InvalidQuantityException
     * @throws InvalidServiceEndTimeException
     */
    public static function fromArray(array $data): self
    {
        $state = self::getDataValue($data, 'state');
        $merchant = Merchant::fromArray(self::getDataValue($data, 'merchant', []));
        $cart = Cart::fromArray(self::getDataValue($data, 'cart', []));
        $deliveryMethod = DeliveryMethod::fromArray(self::getDataValue($data, 'delivery_method', []));
        $customer = Customer::fromArray(self::getDataValue($data, 'customer', []));
        $platform = Platform::fromArray(self::getDataValue($data, 'platform', []));
        $deliveryAddress = Address::fromArray(self::getDataValue($data, 'delivery_address', []));
        $invoiceAddress = Address::fromArray(self::getDataValue($data, 'invoice_address', []));
        $gui = Gui::fromArray(self::getDataValue($data, 'gui', []));

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
            $state,
            $merchant,
            $cart,
            $deliveryMethod,
            $customer,
            $platform,
            $deliveryAddress,
            $invoiceAddress,
            $gui,
            $merchantReference,
            $trackings
        );
    }

    /**
     * @return string
     */
    public function getState(): string
    {
        return $this->state;
    }


    /**
     * @return Cart
     */
    public function getCart(): Cart
    {
        return $this->cart;
    }

    /**
     * @return Gui
     */
    public function getGui(): Gui
    {
        return $this->gui;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return $this->transformPropertiesToAnArray(get_object_vars($this));
    }

    /**
     * Creates a SeQuraOrder instance from request for a given order reference.
     *
     * @param string $reference
     *
     * @return SeQuraOrder
     *
     * @throws InvalidCartItemsException
     */
    public function toSequraOrderInstance(string $reference): SeQuraOrder
    {
        $order = (new SeQuraOrder())
            ->setReference($reference)
            ->setState($this->getState())
            ->setMerchant($this->getMerchant())
            ->setUnshippedCart($this->getCart())
            ->setShippedCart(new Cart($this->getCart()->getCurrency()))
            ->setDeliveryMethod($this->getDeliveryMethod())
            ->setDeliveryAddress($this->getDeliveryAddress())
            ->setInvoiceAddress($this->getInvoiceAddress())
            ->setCustomer($this->getCustomer())
            ->setPlatform($this->getPlatform())
            ->setGui($this->getGui());

        if ($this->getCart()->getCartRef()) {
            $order->setCartId($this->getCart()->getCartRef());
        }

        if ($this->getMerchantReference()) {
            $order->setMerchantReference($this->getMerchantReference());
            $order->setOrderRef1($this->getMerchantReference()->getOrderRef1());
        }

        return $order;
    }
}
