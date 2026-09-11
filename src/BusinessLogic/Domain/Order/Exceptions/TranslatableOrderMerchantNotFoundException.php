<?php

namespace SeQura\Core\BusinessLogic\Domain\Order\Exceptions;

use SeQura\Core\BusinessLogic\Domain\Translations\Model\BaseTranslatableException;
use SeQura\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
use Throwable;

/**
 * Class TranslatableOrderMerchantNotFoundException
 *
 * Translatable counterpart of OrderMerchantNotFoundException, which extends the infrastructure base exception
 * and so carries no label of its own. The API error handling maps one onto the other, so an order stored
 * without a merchant is answered as such rather than as an unhandled error.
 *
 * @package SeQura\Core\BusinessLogic\Domain\Order\Exceptions
 */
class TranslatableOrderMerchantNotFoundException extends BaseTranslatableException
{
    /**
     * @var int
     */
    protected $code = 404;

    /**
     * @param Throwable|null $previous
     */
    public function __construct(?Throwable $previous = null)
    {
        parent::__construct(new TranslatableLabel(
            'Merchant of the requested order is not found.',
            'general.errors.order.merchantNotFound'
        ), $previous);
    }
}
