<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\OrderManagement\Requests;

use SeQura\Core\BusinessLogic\AdminAPI\Request\Request;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Cart;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderUpdateData;

/**
 * Class OrderUpdateRequest
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\OrderManagement\Requests
 */
class OrderUpdateRequest extends Request
{
    /**
     * @var string
     */
    protected $shopOrderReference;

    /**
     * @var Cart|null
     */
    protected $shippedCart;

    /**
     * @var Cart|null
     */
    protected $unshippedCart;

    /**
     * @param string $shopOrderReference Reference the shop knows the order by.
     * @param Cart|null $shippedCart Shipped cart to submit, null to leave the stored one alone.
     * @param Cart|null $unshippedCart Unshipped cart to submit, null to leave the stored one alone.
     */
    public function __construct(
        string $shopOrderReference,
        ?Cart $shippedCart = null,
        ?Cart $unshippedCart = null
    ) {
        $this->shopOrderReference = $shopOrderReference;
        $this->shippedCart = $shippedCart;
        $this->unshippedCart = $unshippedCart;
    }

    /**
     * @inheritDoc
     *
     * @return OrderUpdateData
     */
    public function transformToDomainModel(): object
    {
        return new OrderUpdateData(
            $this->shopOrderReference,
            $this->shippedCart,
            $this->unshippedCart,
            null,
            null
        );
    }
}
