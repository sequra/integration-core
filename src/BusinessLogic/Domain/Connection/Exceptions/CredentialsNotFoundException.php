<?php

namespace SeQura\Core\BusinessLogic\Domain\Connection\Exceptions;

use SeQura\Core\BusinessLogic\Domain\Translations\Model\BaseTranslatableException;
use SeQura\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
use Throwable;

/**
 * Class CredentialsNotFoundException.
 *
 * @package SeQura\Core\BusinessLogic\Domain\Connection\Exceptions
 */
class CredentialsNotFoundException extends BaseTranslatableException
{
    /**
     * @var int
     */
    protected $code = 404 ;

    /**
     * @param ?Throwable $previous
     */
    public function __construct(Throwable $previous = null)
    {
        parent::__construct(new TranslatableLabel(
            'Credentials data not found.',
            'general.errors.connection.credentialsNotFound'
        ), $previous);
    }
}
