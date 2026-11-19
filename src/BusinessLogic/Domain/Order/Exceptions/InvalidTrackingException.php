<?php

namespace SeQura\Core\BusinessLogic\Domain\Order\Exceptions;

use SeQura\Core\BusinessLogic\Domain\Translations\Model\BaseTranslatableException;
use SeQura\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
use Throwable;

/**
 * Class InvalidTrackingException.
 *
 * Raised when an entry in an order update's trackings is not a Tracking instance.
 *
 * @package SeQura\Core\BusinessLogic\Domain\Order\Exceptions
 */
class InvalidTrackingException extends BaseTranslatableException
{
    /**
     * @var int
     */
    protected $code = 400;

    /**
     * @param ?Throwable $previous
     */
    public function __construct(?Throwable $previous = null)
    {
        parent::__construct(new TranslatableLabel(
            'Trackings must be Tracking instances.',
            'general.errors.order.invalidTracking'
        ), $previous);
    }
}
