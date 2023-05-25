<?php

namespace SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Item;

/**
 * Class InvoiceFeeItem
 *
 * @package SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Item
 */
class InvoiceFeeItem extends Item
{
    public function __construct(int $totalWithTax)
    {
        parent::__construct($totalWithTax, ItemType::TYPE_INVOICE_FEE);
    }

    /**
     * Create a new InvoiceFeeItem instance from an array of data.
     *
     * @param array $data Array containing the data.
     *
     * @return Item Returns a new Item instance.
     */
    public static function fromArray(array $data): Item
    {
        return new InvoiceFeeItem(self::getDataValue($data, 'total_with_tax', 0));
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return $this->transformPropertiesToAnArray(get_object_vars($this));
    }
}
