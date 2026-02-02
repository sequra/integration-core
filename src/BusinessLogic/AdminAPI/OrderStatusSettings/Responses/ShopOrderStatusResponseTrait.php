<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\OrderStatusSettings\Responses;

use SeQura\Core\BusinessLogic\Domain\OrderStatus\Models\OrderStatus;

/**
 * Trait ShopOrderStatusResponseTrait
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\OrderStatusSettings\Responses
 */
trait ShopOrderStatusResponseTrait
{
    /**
     * @var OrderStatus[]
     */
    protected $orderStatuses;

    /**
     * @param OrderStatus[]|null $orderStatuses
     */
    public function __construct(?array $orderStatuses)
    {
        $this->orderStatuses = $orderStatuses;
    }

    /**
     * @return mixed[]
     */
    public function toArray(): array
    {
        $statuses = [];
        foreach ($this->orderStatuses as $status) {
            $statuses[] = $status->toArray();
        }

        return $statuses;
    }
}
