<?php

namespace SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Exceptions;

use SeQura\Core\BusinessLogic\Domain\Translations\Model\BaseTranslatableException;
use SeQura\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
use Throwable;

/**
 * Class DuplicatedExpressCheckoutPageException.
 *
 * Raised when two or more ExpressCheckoutPageConfig entries reference the
 * same page in a single ExpressCheckoutSettings aggregate.
 *
 * @package SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Exceptions
 */
class DuplicatedExpressCheckoutPageException extends BaseTranslatableException
{
    /**
     * @var int
     */
    protected $code = 409;

    /**
     * @param ?Throwable $previous
     */
    public function __construct(?Throwable $previous = null)
    {
        parent::__construct(new TranslatableLabel(
            'Duplicate express checkout page.',
            'general.errors.expressCheckout.duplicatePage'
        ), $previous);
    }
}
