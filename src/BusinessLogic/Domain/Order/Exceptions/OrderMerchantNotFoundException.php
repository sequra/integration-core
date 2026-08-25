<?php

namespace SeQura\Core\BusinessLogic\Domain\Order\Exceptions;

use SeQura\Core\Infrastructure\Exceptions\BaseException;

/**
 * Class OrderMerchantNotFoundException
 *
 * Thrown for a stored order whose merchant carries no id. The proxy needs one to pick the credentials it
 * authorizes with, and without this the empty id reaches the credentials lookup, which fails with no way back
 * to the order that caused it.
 *
 * @package SeQura\Core\BusinessLogic\Domain\Order\Exceptions
 */
class OrderMerchantNotFoundException extends BaseException
{
}
