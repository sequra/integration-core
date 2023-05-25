<?php

namespace SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Item;

/**
 * Class ItemType
 *
 * @package SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Item
 */
class ItemType
{
    public const TYPE_PRODUCT = 'product';
    public const TYPE_SERVICE = 'service';
    public const TYPE_OTHER_PAYMENT = 'other_payment';
    public const TYPE_INVOICE_FEE = 'invoice_fee';
    public const TYPE_HANDLING = 'handling';
    public const TYPE_DISCOUNT = 'discount';
}
