<?php

namespace SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Item;

use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\OrderRequestDTO;

/**
 * Class Item
 *
 * @package SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Item
 */
abstract class Item extends OrderRequestDTO
{
    /**
     * @var string|null Item type.
     */
    protected $type;

    /**
     * @var int Price with tax for the amount in the order.
     */
    protected $totalWithTax;

    /**
     * @param int $totalWithTax
     * @param string|null $type
     */
    public function __construct(int $totalWithTax, string $type = null)
    {
        $this->totalWithTax = $totalWithTax;
        $this->type = $type;
    }

    /**
     * Create a new Item instance from an array of data.
     *
     * @param array $data Array containing the data.
     *
     * @return Item Returns a new Item instance.
     */
    public static function fromArray(array $data): Item
    {
        return new static(
            self::getDataValue($data, 'total_with_tax', 0),
            self::getDataValue($data, 'type')
        );
    }


    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @return int
     */
    public function getTotalWithTax(): int
    {
        return $this->totalWithTax;
    }
}
