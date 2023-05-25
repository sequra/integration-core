<?php

namespace SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest;

/**
 * Class OrderRequestStates
 *
 * @package SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest
 */
class OrderRequestStates
{
    public const CONFIRMED = 'confirmed';
    public const ON_HOLD = 'on_hold';
    public const CANCELLED = 'cancelled';
}
