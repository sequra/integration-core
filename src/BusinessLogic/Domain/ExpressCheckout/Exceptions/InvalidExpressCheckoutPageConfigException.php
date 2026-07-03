<?php

namespace SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Exceptions;

use SeQura\Core\BusinessLogic\Domain\Translations\Model\BaseTranslatableException;
use SeQura\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
use Throwable;

/**
 * Class InvalidExpressCheckoutPageConfigException.
 *
 * Raised when an entry in an ExpressCheckoutSettings aggregate is not an
 * instance of ExpressCheckoutPageConfig.
 *
 * @package SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Exceptions
 */
class InvalidExpressCheckoutPageConfigException extends BaseTranslatableException
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
            'Invalid express checkout page config.',
            'general.errors.expressCheckout.invalidPageConfig'
        ), $previous);
    }
}
