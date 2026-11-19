<?php

namespace SeQura\Core\BusinessLogic\Domain\OrderReport\Models;

/**
 * Class OrderDeliveryStates
 *
 * @package SeQura\Core\BusinessLogic\Domain\OrderReport\Models
 */
class OrderDeliveryStates
{
    public const SHIPPED = 'shipped';
    public const DELIVERED = 'delivered';

    /**
     * Returns all available order delivery states.
     *
     * @return string[]
     */
    public static function toArray(): array
    {
        return [self::SHIPPED, self::DELIVERED];
    }
}
