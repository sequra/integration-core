<?php

namespace SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Item;

use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidQuantityException;

/**
 * Class ProductItem
 *
 * @package SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Item
 */
class ProductItem extends Item
{
    /**
     * @var string|int A public product id for this item.
     */
    private $reference;

    /**
     * @var string A description to include in the payment instructions.
     */
    private $name;

    /**
     * @var int Price with tax for one item.
     */
    private $priceWithTax;

    /**
     * @var int The number of items ordered by the shopper.
     */
    private $quantity;

    /**
     * @var boolean Can the buyer access or consume the product without a physical delivery.
     */
    private $downloadable;

    /**
     * @var boolean|null A product is perishable if it loses its value if it is not delivered on time.
     */
    private $perishable;

    /**
     * @var boolean|null A product is personalized if it is irreversibly customized in a way that makes it less
     * appealing to most people other than the buyer.
     */
    private $personalized;

    /**
     * @var boolean|null A product is restockable if it can be sold to someone else if returned in good condition.
     */
    private $restockable;

    /**
     * @var string|null Name of category.
     */
    private $category;

    /**
     * @var string|null Product description.
     */
    private $description;

    /**
     * @var string|null Name of manufacturer.
     */
    private $manufacturer;

    /**
     * @var string|null Name of supplier or provider.
     */
    private $supplier;

    /**
     * @var string|int|null Id from database.
     */
    private $productId;

    /**
     * @var string|null Product page URL in your shop.
     */
    private $url;

    /**
     * @var string|null A reference to the tracking in which this item will be handed.
     */
    private $trackingReference;

    /**
     * @param int|string $reference
     * @param string $name
     * @param int $priceWithTax
     * @param int $quantity
     * @param int $totalWithTax
     * @param bool $downloadable
     * @param bool|null $perishable
     * @param bool|null $personalized
     * @param bool|null $restockable
     * @param string|null $category
     * @param string|null $description
     * @param string|null $manufacturer
     * @param string|null $supplier
     * @param int|string|null $productId
     * @param string|null $url
     * @param string|null $trackingReference
     *
     * @throws InvalidQuantityException
     */
    public function __construct(
        $reference,
        string $name,
        int $priceWithTax,
        int $quantity,
        int $totalWithTax,
        bool $downloadable,
        bool $perishable = null,
        bool $personalized = null,
        bool $restockable = null,
        string $category = null,
        string $description = null,
        string $manufacturer = null,
        string $supplier = null,
        $productId = null,
        string $url = null,
        string $trackingReference = null
    )
    {
        if($quantity < 0) {
            throw new InvalidQuantityException('Quantity cannot be a negative value.');
        }

        parent::__construct($totalWithTax, ItemType::TYPE_PRODUCT);

        $this->reference = $reference;
        $this->name = $name;
        $this->priceWithTax = $priceWithTax;
        $this->quantity = $quantity;
        $this->totalWithTax = $totalWithTax;
        $this->downloadable = $downloadable;
        $this->perishable = $perishable;
        $this->personalized = $personalized;
        $this->restockable = $restockable;
        $this->category = $category;
        $this->description = $description;
        $this->manufacturer = $manufacturer;
        $this->supplier = $supplier;
        $this->productId = $productId;
        $this->url = $url;
        $this->trackingReference = $trackingReference;
    }

    /**
     * @param array $data
     *
     * @return ProductItem
     * @throws InvalidQuantityException
     */
    public static function fromArray(array $data): Item
    {
        return new ProductItem(
            self::getDataValue($data, 'reference'),
            self::getDataValue($data, 'name'),
            self::getDataValue($data, 'price_with_tax', 0),
            self::getDataValue($data, 'quantity', 1),
            self::getDataValue($data, 'total_with_tax', 0),
            self::getDataValue($data, 'downloadable', false),
            self::getDataValue($data, 'perishable', null),
            self::getDataValue($data, 'personalized', null),
            self::getDataValue($data, 'restockable', null),
            self::getDataValue($data, 'category', null),
            self::getDataValue($data, 'description', null),
            self::getDataValue($data, 'manufacturer', null),
            self::getDataValue($data, 'supplier', null),
            self::getDataValue($data, 'product_id', null),
            self::getDataValue($data, 'url', null),
            self::getDataValue($data, 'tracking_reference', null)
        );
    }

    /**
     * @return int|string
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getPriceWithTax(): int
    {
        return $this->priceWithTax;
    }

    /**
     * @return int
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }

    /**
     * @return bool
     */
    public function isDownloadable(): bool
    {
        return $this->downloadable;
    }

    /**
     * @return bool|null
     */
    public function getPerishable(): ?bool
    {
        return $this->perishable;
    }

    /**
     * @return bool|null
     */
    public function getPersonalized(): ?bool
    {
        return $this->personalized;
    }

    /**
     * @return bool|null
     */
    public function getRestockable(): ?bool
    {
        return $this->restockable;
    }

    /**
     * @return string|null
     */
    public function getCategory(): ?string
    {
        return $this->category;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @return string|null
     */
    public function getManufacturer(): ?string
    {
        return $this->manufacturer;
    }

    /**
     * @return string|null
     */
    public function getSupplier(): ?string
    {
        return $this->supplier;
    }

    /**
     * @return int|string|null
     */
    public function getProductId()
    {
        return $this->productId;
    }

    /**
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @return string|null
     */
    public function getTrackingReference(): ?string
    {
        return $this->trackingReference;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return $this->transformPropertiesToAnArray(get_object_vars($this));
    }
}
