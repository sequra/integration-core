<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\OrderStatusSettings\Responses;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\Domain\OrderStatus\Models\OrderStatus;

/**
 * Class ShopOrderStatusResponse
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\OrderStatusSettings\Responses
 */
class ShopOrderStatusResponse extends Response
{
    /**
     * @var OrderStatus[]
     */
    private $orderStatuses;

    /**
     * @param OrderStatus[]|null $orderStatuses
     */
    public function __construct(?array $orderStatuses)
    {
        $this->orderStatuses = $orderStatuses;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $statuses = [];
        foreach ($this->orderStatuses as $status) {
            $statuses[] = [
                'statusId' => $status->getStatusId(),
                'statusName' => $status->getStatusName()
            ];
        }

        return $statuses;
    }
}
