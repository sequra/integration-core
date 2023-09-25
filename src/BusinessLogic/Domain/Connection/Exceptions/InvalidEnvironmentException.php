<?php

namespace SeQura\Core\BusinessLogic\Domain\Connection\Exceptions;

use SeQura\Core\BusinessLogic\Domain\Translations\Model\BaseTranslatableException;
use SeQura\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
use Throwable;

/**
 * Class InvalidEnvironmentException
 *
 * @package SeQura\Core\BusinessLogic\Domain\Connection\Exceptions
 */
class InvalidEnvironmentException extends BaseTranslatableException
{
    protected $code = 401;

    public function __construct(Throwable $previous = null)
    {
        parent::__construct(new TranslatableLabel(
            'Invalid environment type.',
            'general.errors.connection.invalidEnvironment'
        ), $previous);
    }
}
