<?php

namespace SeQura\Core\BusinessLogic\Domain\Order\Exceptions;

use SeQura\Core\BusinessLogic\Domain\Translations\Model\BaseTranslatableException;
use SeQura\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
use Throwable;

/**
 * Class InvalidTrackingTypeException.
 *
 * Raised when tracking data names a type that is not one of the known tracking types.
 *
 * @package SeQura\Core\BusinessLogic\Domain\Order\Exceptions
 */
class InvalidTrackingTypeException extends BaseTranslatableException
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
            'Unknown tracking type.',
            'general.errors.order.invalidTrackingType'
        ), $previous);
    }
}
