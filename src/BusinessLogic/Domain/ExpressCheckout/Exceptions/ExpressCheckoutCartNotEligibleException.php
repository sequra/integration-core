<?php

namespace SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Exceptions;

use SeQura\Core\BusinessLogic\Domain\Translations\Model\BaseTranslatableException;
use SeQura\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
use Throwable;

/**
 * @package SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Exceptions
 */
class ExpressCheckoutCartNotEligibleException extends BaseTranslatableException
{
    /**
     * @var int
     */
    protected $code = 422;

    /**
     * @param ?Throwable $previous
     */
    public function __construct(?Throwable $previous = null)
    {
        parent::__construct(new TranslatableLabel(
            'The Express Checkout cart is not eligible after the change.',
            'general.errors.expressCheckout.cartNotEligible'
        ), $previous);
    }
}
