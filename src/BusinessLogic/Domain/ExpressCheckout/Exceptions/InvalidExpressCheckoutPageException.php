<?php

namespace SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Exceptions;

use SeQura\Core\BusinessLogic\Domain\Translations\Model\BaseTranslatableException;
use SeQura\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
use Throwable;

/**
 * Class InvalidExpressCheckoutPageException.
 *
 * @package SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Exceptions
 */
class InvalidExpressCheckoutPageException extends BaseTranslatableException
{
    /**
     * @var int
     */
    protected $code = 404;

    /**
     * @param ?Throwable $previous
     */
    public function __construct(?Throwable $previous = null)
    {
        parent::__construct(new TranslatableLabel(
            'Invalid express checkout page.',
            'general.errors.expressCheckout.invalidPage'
        ), $previous);
    }
}
