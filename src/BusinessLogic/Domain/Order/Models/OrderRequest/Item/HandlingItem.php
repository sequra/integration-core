<?php

namespace SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Item;

/**
 * Class HandlingItem
 *
 * @package SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Item
 */
class HandlingItem extends Item
{
    /**
     * @var string|int A unique code that refers to this item.
     */
    protected $reference;

    /**
     * @var string A name to describe this item.
     */
    protected $name;

    /**
     * @param int|string $reference
     * @param string $name
     * @param int $totalWithTax
     */
    public function __construct($reference, string $name, int $totalWithTax)
    {
        parent::__construct($totalWithTax, ItemType::TYPE_HANDLING);

        $this->reference = $reference;
        $this->name = $name;
    }

    /**
     * Create a new HandlingItem instance from an array of properties.
     *
     * @param array $data An associative array of HandlingItem properties.
     *
     * @return HandlingItem A new HandlingItem instance with the given properties.
     */
    public static function fromArray(array $data): Item
    {
        $reference = self::getDataValue($data, 'reference');
        $name = self::getDataValue($data, 'name');
        $totalWithTax = self::getDataValue($data, 'total_with_tax', 0);

        return new HandlingItem($reference, $name, $totalWithTax);
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
