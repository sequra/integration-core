<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\OrderManagement\Responses;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\Domain\Order\Models\SeQuraOrder;

/**
 * Class OrderUpdateResponse
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\OrderManagement\Responses
 */
class OrderUpdateResponse extends Response
{
    /**
     * @var SeQuraOrder
     */
    protected $order;

    /**
     * @param SeQuraOrder $order Order as seQura holds it after the update.
     */
    public function __construct(SeQuraOrder $order)
    {
        $this->order = $order;
    }

    /**
     * @return SeQuraOrder
     */
    public function getOrder(): SeQuraOrder
    {
        return $this->order;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return $this->order->toArray();
    }
}
