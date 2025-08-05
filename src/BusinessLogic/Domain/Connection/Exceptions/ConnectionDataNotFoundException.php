<?php

namespace SeQura\Core\BusinessLogic\Domain\Connection\Exceptions;

use SeQura\Core\BusinessLogic\Domain\Translations\Model\BaseTranslatableException;
use SeQura\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
use Throwable;

/**
 * Class ConnectionDataNotFoundException.
 *
 * @package SeQura\Core\BusinessLogic\Domain\Connection\Exceptions
 */
class ConnectionDataNotFoundException extends BaseTranslatableException
{
    /**
     * @var int
     */
    protected $code = 404 ;

    /**
     * @param ?Throwable $previous
     */
    public function __construct(?Throwable $previous = null)
    {
        parent::__construct(new TranslatableLabel(
            'Connection data not found.',
            'general.errors.connection.connectionNotFound'
        ), $previous);
    }
}
