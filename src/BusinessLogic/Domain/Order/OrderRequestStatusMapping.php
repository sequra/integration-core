<?php

namespace SeQura\Core\BusinessLogic\Domain\Order;

use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidOrderStateException;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\OrderRequestStates;

/**
 * Class OrderRequestStatusMapping
 *
 * @package SeQura\Core\BusinessLogic\Domain\Order
 */
class OrderRequestStatusMapping
{
    /**
     * Map of seQura response states to seQura request states.
     *
     * @var array
     */
    protected static $statusMap = [
        OrderStates::STATE_APPROVED => OrderRequestStates::CONFIRMED,
        OrderStates::STATE_NEEDS_REVIEW => OrderRequestStates::ON_HOLD,
        OrderStates::STATE_CANCELLED => OrderRequestStates::CANCELLED
    ];

    /**
     * Maps order states gotten from SeQura to states expected by SeQura
     *
     * @param string $status
     *
     * @return string
     *
     * @throws InvalidOrderStateException
     */
    public static function mapOrderRequestStatus(string $status): string
    {
        if (!array_key_exists($status, self::$statusMap)) {
            throw new InvalidOrderStateException("Invalid order state '{$status}'", 400);
        }

        return self::$statusMap[$status];
    }
}
