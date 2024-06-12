<?php

namespace SeQura\Core\BusinessLogic\Domain\Order;

/**
 * Class OrderStates
 *
 * @package SeQura\Core\BusinessLogic\Domain\Order
 */
class OrderStates
{
    public const STATE_SOLICITED = 'solicited';
    public const STATE_APPROVED = 'approved';
    public const STATE_NEEDS_REVIEW = 'needs_review';
    public const STATE_CANCELLED = 'cancelled';

    /**
     * Returns all SeQura order statuses.
     *
     * @return string[]
     */
    public static function toArray(): array
    {
        return [
            self::STATE_SOLICITED,
            self::STATE_APPROVED,
            self::STATE_NEEDS_REVIEW,
            self::STATE_CANCELLED
        ];
    }
}
