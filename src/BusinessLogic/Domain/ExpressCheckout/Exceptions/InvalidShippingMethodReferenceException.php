<?php

namespace SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Exceptions;

use SeQura\Core\BusinessLogic\Domain\Translations\Model\BaseTranslatableException;
use SeQura\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
use Throwable;

/**
 * @package SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Exceptions
 */
class InvalidShippingMethodReferenceException extends BaseTranslatableException
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
            'The shipping method is not offered for this cart.',
            'general.errors.expressCheckout.invalidShippingMethodReference'
        ), $previous);
    }
}
