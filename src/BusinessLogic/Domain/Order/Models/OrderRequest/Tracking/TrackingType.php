<?php

namespace SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Tracking;

/**
 * Class TrackingType
 *
 * @package SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Tracking
 */
class TrackingType
{
    public const TYPE_PICKUP_POINT = 'pickup_point';
    public const TYPE_PICKUP_STORE = 'pickup_store';
    public const TYPE_POSTAL = 'postal';
}
