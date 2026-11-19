<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\OrderManagement\Requests;

use SeQura\Core\BusinessLogic\AdminAPI\Request\Request;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidTrackingException;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Address;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Cart;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Tracking\Tracking;
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
     * @var Address|null
     */
    protected $deliveryAddress;

    /**
     * @var Address|null
     */
    protected $invoiceAddress;

    /**
     * @var Tracking[]|null
     */
    protected $trackings;

    /**
     * @param string $shopOrderReference Reference the shop knows the order by.
     * @param Cart|null $shippedCart Shipped cart to submit, null to leave the stored one alone.
     * @param Cart|null $unshippedCart Unshipped cart to submit, null to leave the stored one alone.
     * @param Address|null $deliveryAddress Delivery address to submit, null to leave the stored one alone.
     * @param Address|null $invoiceAddress Invoice address to submit, null to leave the stored one alone.
     * @param Tracking[]|null $trackings Trackings to submit, null to leave the stored ones alone.
     */
    public function __construct(
        string $shopOrderReference,
        ?Cart $shippedCart = null,
        ?Cart $unshippedCart = null,
        ?Address $deliveryAddress = null,
        ?Address $invoiceAddress = null,
        ?array $trackings = null
    ) {
        $this->shopOrderReference = $shopOrderReference;
        $this->shippedCart = $shippedCart;
        $this->unshippedCart = $unshippedCart;
        $this->deliveryAddress = $deliveryAddress;
        $this->invoiceAddress = $invoiceAddress;
        $this->trackings = $trackings;
    }

    /**
     * @inheritDoc
     *
     * @return OrderUpdateData
     *
     * @throws InvalidTrackingException When a tracking is not a Tracking instance.
     */
    public function transformToDomainModel(): object
    {
        return new OrderUpdateData(
            $this->shopOrderReference,
            $this->shippedCart,
            $this->unshippedCart,
            $this->deliveryAddress,
            $this->invoiceAddress,
            $this->trackings
        );
    }
}
