<?php

namespace SeQura\Core\BusinessLogic\Domain\Connection\Exceptions;

use SeQura\Core\BusinessLogic\Domain\Translations\Model\BaseTranslatableException;
use SeQura\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
use Throwable;

/**
 * Class BadMerchantIdException
 *
 * @package SeQura\Core\BusinessLogic\Domain\Connection\Exceptions
 */
class BadMerchantIdException extends BaseTranslatableException
{
    protected $code = 403;

    public function __construct(Throwable $previous = null)
    {
        parent::__construct(new TranslatableLabel(
            'Provided merchantId is invalid.',
            'general.errors.connection.invalidMerchantId'
        ), $previous);
    }
}
