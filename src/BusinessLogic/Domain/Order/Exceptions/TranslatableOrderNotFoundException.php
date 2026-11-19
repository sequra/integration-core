<?php

namespace SeQura\Core\BusinessLogic\Domain\Order\Exceptions;

use SeQura\Core\BusinessLogic\Domain\Translations\Model\BaseTranslatableException;
use SeQura\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
use Throwable;

/**
 * Class TranslatableOrderNotFoundException
 *
 * Translatable counterpart of OrderNotFoundException, which extends the infrastructure base exception and so
 * carries no label of its own. The API error handling maps one onto the other, so a request for an order the
 * integration does not hold is answered as a missing order rather than as an unhandled error.
 *
 * @package SeQura\Core\BusinessLogic\Domain\Order\Exceptions
 */
class TranslatableOrderNotFoundException extends BaseTranslatableException
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
            'Requested order is not found.',
            'general.errors.order.notFound'
        ), $previous);
    }
}
