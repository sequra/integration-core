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
}
