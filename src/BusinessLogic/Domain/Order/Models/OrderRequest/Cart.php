<?php

namespace SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest;

use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidCartItemsException;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidDateException;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidDurationException;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidQuantityException;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidServiceEndTimeException;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Item\AbstractItemFactory;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Item\DiscountItem;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Item\HandlingItem;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Item\Item;
use SeQura\Core\Infrastructure\ServiceRegister;

/**
 * Class Cart
 *
 * @package SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest
 */
class Cart extends OrderRequestDTO
{
    /**
     * @var string Name of currency used on the purchase.
     */
    protected $currency;

    /**
     * @var boolean Set to true if shopper has indicated that this is a gift.
     */
    protected $gift;

    /**
     * @var int Total value with tax of the items listed below.
     */
    protected $orderTotalWithTax;

    /**
     * @var string|int|null Shop's unique id for this basket.
     */
    protected $cartRef;

    /**
     * @var string|null When shopper put the first item in the cart.
     */
    protected $createdAt;

    /**
     * @var string|null When shopper put the last item in the cart.
     */
    protected $updatedAt;

    /**
     * @var Item[] List of items in the order.
     */
    protected $items;

    /**
     * @param string $currency
     * @param bool $gift
     * @param array $items
     * @param string|int|null $cartRef
     * @param string|null $createdAt
     * @param string|null $updatedAt
     *
     * @throws InvalidCartItemsException
     */
    public function __construct(
        string $currency,
        bool $gift = false,
        array $items = [],
        $cartRef = null,
        string $createdAt = null,
        string $updatedAt = null
    ) {
        $orderTotalWithTax = 0;

        foreach ($items as $item) {
            $orderTotalWithTax += $item->getTotalWithTax();
        }

        $this->currency = $currency;
        $this->gift = $gift;
        $this->orderTotalWithTax = $orderTotalWithTax;
        $this->items = $items;
        $this->cartRef = $cartRef;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    /**
     * Create a new Cart instance from an array of data.
     *
     * @param array $data Array containing the data.
     *
     * @return Cart Returns a new Cart instance.
     * @throws InvalidCartItemsException
     * @throws InvalidQuantityException
     * @throws InvalidDateException
     * @throws InvalidDurationException
     * @throws InvalidServiceEndTimeException
     */
    public static function fromArray(array $data): Cart
    {
        $orderTotal = self::getDataValue($data, 'order_total_with_tax', 0);
        $items = (array) self::getDataValue($data, 'items', []);

        /**
         * @var AbstractItemFactory $itemFactory
         */
        $itemFactory = ServiceRegister::getService(AbstractItemFactory::class);
        $itemInstances = $itemFactory->createListFromArray($items);

        $itemTotal = array_reduce($itemInstances, static function ($sum, $item) {
            return $sum + $item->getTotalWithTax();
        }, 0);

        $diff = $orderTotal - $itemTotal;

        if ($diff < 0) {
            $itemInstances[] = new DiscountItem('additional_discount', 'Discount', $diff);
        }

        if ($diff > 0) {
            $itemInstances[] = new HandlingItem('additional_handling', 'Surcharge', $diff);
        }

        return new self(
            self::getDataValue($data, 'currency'),
            self::getDataValue($data, 'gift', false),
            $itemInstances,
            self::getDataValue($data, 'cart_ref', null),
            self::getDataValue($data, 'created_at', null),
            self::getDataValue($data, 'updated_at', null)
        );
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @return bool
     */
    public function isGift(): bool
    {
        return $this->gift;
    }

    /**
     * @return int
     */
    public function getOrderTotalWithTax(): int
    {
        return $this->orderTotalWithTax;
    }

    /**
     * @return int|string|null
     */
    public function getCartRef()
    {
        return $this->cartRef;
    }

    /**
     * @return string|null
     */
    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    /**
     * @return string|null
     */
    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }

    /**
     * @return array|Item[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @param Item[] $items
     *
     * @return void
     */
    public function setItems(array $items)
    {
        $orderTotalWithTax = 0;

        foreach ($items as $item) {
            $orderTotalWithTax += $item->getTotalWithTax();
        }

        $this->items = $items;
        $this->orderTotalWithTax = $orderTotalWithTax;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return $this->transformPropertiesToAnArray(get_object_vars($this));
    }
}
