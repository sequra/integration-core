<?php

namespace SeQura\Core\BusinessLogic\Domain\Order;

/**
 * Class OrderStates
 *
 * @package SeQura\Core\BusinessLogic\Domain\Order
 */
class OrderStates
{
    /**
     * SeQura solicited order statuses constant.
     */
    public const STATE_SOLICITED = 'solicited';
    /**
     * SeQura approved order statuses constant.
     */
    public const STATE_APPROVED = 'approved';
    /**
     * SeQura needs review order statuses constant.
     */
    public const STATE_NEEDS_REVIEW = 'needs_review';
    /**
     * SeQura cancelled order statuses constant.
     */
    public const STATE_CANCELLED = 'cancelled';
    /**
     * SeQura shipped order statuses constant.
     */
    public const STATE_SHIPPED = 'shipped';

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
            self::STATE_CANCELLED,
            self::STATE_SHIPPED
        ];
    }
}
