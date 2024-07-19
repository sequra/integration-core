<?php

namespace SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Item;

use InvalidArgumentException;

/**
 * AbstractItemFactory implementation
 *
 * @package SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Item
 */
class ItemFactory extends AbstractItemFactory
{
    /**
     * Create Item object from array.
     *
     * @param array<string, mixed> $itemData
     *
     * @throws InvalidArgumentException
     */
    public function createFromArray(array $itemData): Item
    {
        $type = $itemData['type'] ?? null;
        switch ($type) {
            case ItemType::TYPE_PRODUCT:
                return ProductItem::fromArray($itemData);
            case ItemType::TYPE_HANDLING:
                return HandlingItem::fromArray($itemData);
            case ItemType::TYPE_DISCOUNT:
                return DiscountItem::fromArray($itemData);
            case ItemType::TYPE_SERVICE:
                return ServiceItem::fromArray($itemData);
            case ItemType::TYPE_INVOICE_FEE:
                return InvoiceFeeItem::fromArray($itemData);
            case ItemType::TYPE_OTHER_PAYMENT:
                return OtherPaymentItem::fromArray($itemData);
            default:
                throw new InvalidArgumentException('Invalid cart item type ' . $type);
        }
    }
}
