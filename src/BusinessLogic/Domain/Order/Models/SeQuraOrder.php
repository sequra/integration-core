<?php

namespace SeQura\Core\BusinessLogic\Domain\Order\Models;

use Exception;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Address;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Cart;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Customer;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\DeliveryMethod;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Gui;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Merchant;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\MerchantReference;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Platform;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Tracking\Tracking;
use SeQura\Core\Infrastructure\ORM\Configuration\EntityConfiguration;
use SeQura\Core\Infrastructure\ORM\Configuration\IndexMap;
use SeQura\Core\Infrastructure\ORM\Entity;

/**
 * Class SeQuraOrder
 *
 * @package SeQura\Core\BusinessLogic\Domain\Order\Models
 */
class SeQuraOrder extends Entity
{
    /**
     * Fully qualified name of this class.
     */
    public const CLASS_NAME = __CLASS__;

    /**
     * Array of field names.
     *
     * @var array
     */
    protected $fields = [
        'id',
        'reference',
        'cartId',
        'orderRef1',
        'merchant',
        'merchantReference',
        'shippedCart',
        'unshippedCart',
        'state',
        'trackings',
        'deliveryMethod',
        'deliveryAddress',
        'invoiceAddress',
        'customer',
        'platform',
        'gui',
        'paymentMethod'
    ];

    /**
     * @var string SeQura order reference
     */
    protected $reference;

    /**
     * @var string Cart identifier
     */
    protected $cartId;

    /**
     * @var string External order reference, denoting the ID of that order in the shop system
     */
    protected $orderRef1 = '';

    /**
     * @var Merchant Merchant details
     */
    protected $merchant;

    /**
     * @var MerchantReference Merchant reference for the SeQura and shop order
     */
    protected $merchantReference;

    /**
     * @var Cart Shipped cart
     */
    protected $shippedCart;

    /**
     * @var Cart Unshipped cart
     */
    protected $unshippedCart;

    /**
     * @var string Current order state on SeQura
     */
    protected $state;

    /**
     * @var Tracking[] Trackings for the order
     */
    protected $trackings;

    /**
     * @var DeliveryMethod Information about the delivery method
     */
    protected $deliveryMethod;

    /**
     * @var Address Information about the delivery address
     */
    protected $deliveryAddress;

    /**
     * @var Address Information about the invoice address
     */
    protected $invoiceAddress;

    /**
     * @var Customer Information about the customer
     */
    protected $customer;

    /**
     * @var Platform Platform information
     */
    protected $platform;

    /**
     * @var Gui GUI information
     */
    protected $gui;

    /**
     * @var PaymentMethod|null Payment method
     */
    protected $paymentMethod;

    /**
     * @inheritDoc
     */
    public function getConfig(): EntityConfiguration
    {
        $indexMap = new IndexMap();

        $indexMap->addStringIndex('reference')
            ->addStringIndex('cartId')
            ->addStringIndex('orderRef1');

        return new EntityConfiguration($indexMap, 'SeQuraOrder');
    }

    /**
     * Sets raw array data to this entity instance properties.
     *
     * @param array $data Raw array data with keys for class fields. @see self::$fields for field names.
     *
     * @throws Exception
     */
    public function inflate(array $data): void
    {
        parent::inflate($data);

        $this->reference = $data['reference'] ?? '';
        $this->cartId = $data['cartId'] ?? '';
        $this->orderRef1 = $data['orderRef1'] ?? '';
        $this->merchant = Merchant::fromArray($data['merchant']);
        $this->merchantReference = MerchantReference::fromArray($data['merchant_reference']);
        $this->shippedCart = Cart::fromArray($data['shipped_cart']);
        $this->unshippedCart = Cart::fromArray($data['unshipped_cart']);
        $this->deliveryMethod = DeliveryMethod::fromArray($data['delivery_method']);
        $this->deliveryAddress = Address::fromArray($data['delivery_address']);
        $this->invoiceAddress = Address::fromArray($data['invoice_address']);
        $this->customer = Customer::fromArray($data['customer']);
        $this->platform = Platform::fromArray($data['platform']);
        $this->gui = Gui::fromArray($data['gui']);
        $this->state = $data['state'] ?? '';

        if (!empty($data['trackings'])) {
            $this->trackings = Tracking::fromBatch($data['trackings']);
        }

        if (!empty($data['payment_method'])) {
            $this->paymentMethod = PaymentMethod::fromArray($data['payment_method']);
        }
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $data = parent::toArray();

        $data['reference'] = $this->reference;
        $data['cart_id'] = $this->cartId;
        $data['order_ref_1'] = $this->orderRef1;
        $data['state'] = $this->state;
        $data['merchant'] = $this->merchant ? $this->merchant->toArray() : [];
        $data['merchant_reference'] = $this->merchantReference ? $this->merchantReference->toArray() : [];
        $data['shipped_cart'] = $this->shippedCart ? $this->shippedCart->toArray() : [];
        $data['unshipped_cart'] = $this->unshippedCart ? $this->unshippedCart->toArray() : [];
        $data['delivery_method'] = $this->deliveryMethod ? $this->deliveryMethod->toArray() : [];
        $data['delivery_address'] = $this->deliveryAddress ? $this->deliveryAddress->toArray() : [];
        $data['invoice_address'] = $this->invoiceAddress ? $this->invoiceAddress->toArray() : [];
        $data['customer'] = $this->customer ? $this->customer->toArray() : [];
        $data['platform'] = $this->platform ? $this->platform->toArray() : [];
        $data['payment_method'] = $this->paymentMethod ? $this->paymentMethod->toArray() : [];
        $data['gui'] = $this->gui ? $this->gui->toArray() : [];

        if (!empty($this->trackings)) {
            $data['trackings'] = [];

            foreach ($this->trackings as $tracking) {
                $data['trackings'][] = $tracking->toArray();
            }
        }

        return $data;
    }

    /**
     * Get the value of reference
     *
     * @return string
     */
    public function getReference(): string
    {
        return $this->reference;
    }

    /**
     * Set the value of reference
     *
     * @param string $reference
     *
     * @return SeQuraOrder
     */
    public function setReference(string $reference): SeQuraOrder
    {
        $this->reference = $reference;

        return $this;
    }

    /**
     * Get the value of cart
     *
     * @return string
     */
    public function getCartId(): string
    {
        return $this->cartId;
    }

    /**
     * Set the value of cart
     *
     * @param string $cartId
     *
     * @return SeQuraOrder
     */
    public function setCartId(string $cartId): SeQuraOrder
    {
        $this->cartId = $cartId;

        return $this;
    }

    /**
     * Get the value of orderRef1
     *
     * @return string
     */
    public function getOrderRef1(): string
    {
        return $this->orderRef1;
    }

    /**
     * Set the value of orderRef1.
     *
     * @param string $orderRef1
     *
     * @return SeQuraOrder
     */
    public function setOrderRef1(string $orderRef1): SeQuraOrder
    {
        $this->orderRef1 = $orderRef1;

        return $this;
    }

    /**
     * Get the value of status.
     *
     * @return string
     */
    public function getState(): string
    {
        return $this->state;
    }

    /**
     * Set the value of status.
     *
     * @param string $state
     *
     * @return SeQuraOrder
     */
    public function setState(string $state): SeQuraOrder
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get the value of merchant.
     *
     * @return Merchant
     */
    public function getMerchant(): Merchant
    {
        return $this->merchant;
    }

    /**
     * Set the value of merchant.
     *
     * @param Merchant $merchant
     *
     * @return SeQuraOrder
     */
    public function setMerchant(Merchant $merchant): SeQuraOrder
    {
        $this->merchant = $merchant;

        return $this;
    }

    /**
     * Get the value of merchant reference.
     *
     * @return MerchantReference
     */
    public function getMerchantReference(): MerchantReference
    {
        return $this->merchantReference;
    }

    /**
     * Set the value of merchant reference.
     *
     * @param MerchantReference $merchantReference
     *
     * @return SeQuraOrder
     */
    public function setMerchantReference(MerchantReference $merchantReference): SeQuraOrder
    {
        $this->merchantReference = $merchantReference;

        return $this;
    }

    /**
     * Get the value of trackings.
     *
     * @return Tracking[]
     */
    public function getTrackings(): array
    {
        return $this->trackings ?? [];
    }

    /**
     * Set the value of trackings.
     *
     * @param Tracking[] $trackings
     *
     * @return SeQuraOrder
     */
    public function setTrackings(array $trackings): SeQuraOrder
    {
        $this->trackings = $trackings;

        return $this;
    }

    /**
     * Return the value of delivery method.
     *
     * @return DeliveryMethod
     */
    public function getDeliveryMethod(): DeliveryMethod
    {
        return $this->deliveryMethod;
    }

    /**
     * Set the value of delivery method.
     *
     * @param DeliveryMethod $deliveryMethod
     *
     * @return SeQuraOrder
     */
    public function setDeliveryMethod(DeliveryMethod $deliveryMethod): SeQuraOrder
    {
        $this->deliveryMethod = $deliveryMethod;

        return $this;
    }

    /**
     * Return the value of shipped cart.
     *
     * @return Cart
     */
    public function getShippedCart(): Cart
    {
        return $this->shippedCart;
    }

    /**
     * Set the value of shipped cart.
     *
     * @param Cart $shippedCart
     *
     * @return SeQuraOrder
     */
    public function setShippedCart(Cart $shippedCart): SeQuraOrder
    {
        $this->shippedCart = $shippedCart;

        return $this;
    }

    /**
     * Return the value of unshipped cart.
     *
     * @return Cart
     */
    public function getUnshippedCart(): Cart
    {
        return $this->unshippedCart;
    }

    /**
     * Set the value of unshipped cart.
     *
     * @param Cart $unshippedCart
     *
     * @return SeQuraOrder
     */
    public function setUnshippedCart(Cart $unshippedCart): SeQuraOrder
    {
        $this->unshippedCart = $unshippedCart;

        return $this;
    }

    /**
     * Return the value of delivery address.
     *
     * @return Address
     */
    public function getDeliveryAddress(): Address
    {
        return $this->deliveryAddress;
    }

    /**
     * Set the value of delivery address.
     *
     * @param Address $deliveryAddress
     *
     * @return SeQuraOrder
     */
    public function setDeliveryAddress(Address $deliveryAddress): SeQuraOrder
    {
        $this->deliveryAddress = $deliveryAddress;

        return $this;
    }

    /**
     * Return the value of invoice address.
     *
     * @return Address
     */
    public function getInvoiceAddress(): Address
    {
        return $this->invoiceAddress;
    }

    /**
     * Set the value of invoice address.
     *
     * @param Address $invoiceAddress
     *
     * @return SeQuraOrder
     */
    public function setInvoiceAddress(Address $invoiceAddress): SeQuraOrder
    {
        $this->invoiceAddress = $invoiceAddress;

        return $this;
    }

    /**
     * Return the value of customer.
     *
     * @return Customer
     */
    public function getCustomer(): Customer
    {
        return $this->customer;
    }

    /**
     * Set the value of customer.
     *
     * @param Customer $customer
     *
     * @return SeQuraOrder
     */
    public function setCustomer(Customer $customer): SeQuraOrder
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * Return the value of platform.
     *
     * @return Platform
     */
    public function getPlatform(): Platform
    {
        return $this->platform;
    }

    /**
     * Set the value of platform.
     *
     * @param Platform $platform
     *
     * @return SeQuraOrder
     */
    public function setPlatform(Platform $platform): SeQuraOrder
    {
        $this->platform = $platform;

        return $this;
    }

    /**
     * Return the value of GUI
     *
     * @return Gui
     */
    public function getGui(): Gui
    {
        return $this->gui;
    }

    /**
     * Set the value of GUI
     *
     * @param Gui $gui
     *
     * @return SeQuraorder
     */
    public function setGui(Gui $gui): SeQuraOrder
    {
        $this->gui = $gui;

        return $this;
    }

    /**
     * Return the value of payment method
     *
     * @return PaymentMethod|null
     */
    public function getPaymentMethod(): ?PaymentMethod
    {
        return $this->paymentMethod;
    }

    /**
     * Sets the value of payment method
     *
     * @param PaymentMethod|null $paymentMethod
     *
     * @return void
     */
    public function setPaymentMethod(?PaymentMethod $paymentMethod): void
    {
        $this->paymentMethod = $paymentMethod;
    }
}
