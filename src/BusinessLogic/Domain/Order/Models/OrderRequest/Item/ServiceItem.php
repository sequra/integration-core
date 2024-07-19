<?php

namespace SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Item;

use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidDateException;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidDurationException;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidQuantityException;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidServiceEndTimeException;
use SeQura\Core\BusinessLogic\Utility\StringValidator;

/**
 * Class ServiceItem
 *
 * @package SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Item
 */
class ServiceItem extends Item
{
    /**
     * @var string|int A public id for this service.
     */
    protected $reference;

    /**
     * @var string A name to describe this service.
     */
    protected $name;

    /**
     * @var string|null Maximum date for the service to be rendered or ended (ISO8601 formatted date).
     */
    protected $endsOn;

    /**
     * @var string|null Maximum time, from the start of the service, for the service to be rendered or ended.
     */
    protected $endsIn;

    /**
     * @var int Price with tax for one item.
     */
    protected $priceWithTax;

    /**
     * @var int The number of items ordered by the shopper.
     */
    protected $quantity;

    /**
     * @var boolean True for services that can be fully (or sufficiently) enjoyed without a physical delivery.
     */
    protected $downloadable;

    /**
     * @var string|null Name of supplier or provider.
     */
    protected $supplier;

    /**
     * @var boolean|null True when the service has been rendered.
     */
    protected $rendered;

    /**
     * @param $reference
     * @param string $name
     * @param int $priceWithTax
     * @param int $quantity
     * @param bool $downloadable
     * @param int $totalWithTax
     * @param string|null $endsOn
     * @param string|null $endsIn
     * @param string|null $supplier
     * @param bool|null $rendered
     *
     * @throws InvalidServiceEndTimeException
     * @throws InvalidDurationException
     * @throws InvalidDateException
     * @throws InvalidQuantityException
     */
    public function __construct(
        $reference,
        string $name,
        int $priceWithTax,
        int $quantity,
        bool $downloadable,
        int $totalWithTax,
        ?string $endsOn,
        ?string $endsIn,
        ?string $supplier,
        ?bool $rendered
    ) {
        if ((!$endsOn && !$endsIn) || ($endsOn && $endsIn)) {
            throw new InvalidServiceEndTimeException('Exactly one of endsOn or endsIn should be set.');
        }

        if ($endsIn && !StringValidator::isISO8601Duration($endsIn)) {
            throw new InvalidDurationException('EndsIn must be a valid ISO 8601 formatted duration.');
        }

        if ($endsOn && !StringValidator::isISO8601Date($endsOn)) {
            throw new InvalidDateException('EndsOn must be a valid ISO 8601 formatted date.');
        }

        if ($quantity < 0) {
            throw new InvalidQuantityException('Quantity cannot be a negative value.');
        }

        parent::__construct($totalWithTax, ItemType::TYPE_SERVICE);

        $this->reference = $reference;
        $this->name = $name;
        $this->priceWithTax = $priceWithTax;
        $this->quantity = $quantity;
        $this->downloadable = $downloadable;
        $this->endsOn = $endsOn;
        $this->endsIn = $endsIn;
        $this->supplier = $supplier;
        $this->rendered = $rendered;
    }

    /**
     * @inheritDoc
     *
     * @throws InvalidDateException
     * @throws InvalidDurationException
     * @throws InvalidQuantityException
     * @throws InvalidServiceEndTimeException
     */
    public static function fromArray(array $data): Item
    {
        $reference = self::getDataValue($data, 'reference');
        $name = self::getDataValue($data, 'name');
        $priceWithTax = self::getDataValue($data, 'price_with_tax', 0);
        $quantity = self::getDataValue($data, 'quantity', 1);
        $downloadable = self::getDataValue($data, 'downloadable', false);
        $totalWithTax = self::getDataValue($data, 'total_with_tax', 0);
        $endsOn = self::getDataValue($data, 'ends_on', null);
        $endsIn = self::getDataValue($data, 'ends_in', null);
        $supplier = self::getDataValue($data, 'supplier');
        $rendered = self::getDataValue($data, 'rendered');

        return new self(
            $reference,
            $name,
            $priceWithTax,
            $quantity,
            $downloadable,
            $totalWithTax,
            $endsOn,
            $endsIn,
            $supplier,
            $rendered
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
     * @return string|null
     */
    public function getEndsOn(): ?string
    {
        return $this->endsOn;
    }

    /**
     * @return string|null
     */
    public function getEndsIn(): ?string
    {
        return $this->endsIn;
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
     * @return string|null
     */
    public function getSupplier(): ?string
    {
        return $this->supplier;
    }

    /**
     * @return bool|null
     */
    public function getRendered(): ?bool
    {
        return $this->rendered;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return $this->transformPropertiesToAnArray(get_object_vars($this));
    }
}
