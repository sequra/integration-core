<?php

namespace SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Item;

/**
 * Class DiscountItem
 *
 * @package SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Item
 */
class DiscountItem extends Item
{
    /**
     * @var string|int A unique code that refers to this discount.
     */
    protected $reference;

    /**
     * @var string A name to describe this discount.
     */
    protected $name;

    /**
     * @param int|string $reference
     * @param string $name
     * @param int $totalWithTax
     */
    public function __construct($reference, string $name, int $totalWithTax)
    {
        parent::__construct($totalWithTax, ItemType::TYPE_DISCOUNT);

        $this->reference = $reference;
        $this->name = $name;
    }

    /**
     * Create DiscountItem object from array.
     *
     * @param array $data
     *
     * @return DiscountItem
     */
    public static function fromArray(array $data): Item
    {
        $totalWithTax = self::getDataValue($data, 'total_with_tax', 0);
        $reference = self::getDataValue($data, 'reference');
        $name = self::getDataValue($data, 'name');

        return new self($reference, $name, $totalWithTax);
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
     * @inheritDoc
     */
    public function toArray(): array
    {
        return $this->transformPropertiesToAnArray(get_object_vars($this));
    }
}
