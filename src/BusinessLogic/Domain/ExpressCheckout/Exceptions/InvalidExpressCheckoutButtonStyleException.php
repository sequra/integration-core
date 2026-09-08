<?php

namespace SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Exceptions;

use SeQura\Core\BusinessLogic\Domain\Translations\Model\BaseTranslatableException;
use SeQura\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
use Throwable;

/**
 * Class InvalidExpressCheckoutButtonStyleException.
 *
 * @package SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Exceptions
 */
class InvalidExpressCheckoutButtonStyleException extends BaseTranslatableException
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
            'Invalid express checkout button style.',
            'general.errors.expressCheckout.invalidButtonStyle'
        ), $previous);
    }
}
