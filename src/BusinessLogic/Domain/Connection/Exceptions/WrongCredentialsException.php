<?php

namespace SeQura\Core\BusinessLogic\Domain\Connection\Exceptions;

use SeQura\Core\BusinessLogic\Domain\Translations\Model\BaseTranslatableException;
use SeQura\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
use Throwable;

/**
 * Class WrongCredentialsException
 *
 * @package SeQura\Core\BusinessLogic\Domain\Connection\Exceptions
 */
class WrongCredentialsException extends BaseTranslatableException
{
    /**
     * @var int
     */
    protected $code = 401;

    /**
     * @param Throwable|null $previous
     * @param string[] $deployment
     */
    public function __construct(?Throwable $previous = null, array $deployment = [])
    {
        $message = 'Invalid username or password';


        if (!empty($deployment)) {
            $deploymentList = implode(',', $deployment);
            $message = 'deployment/' . $deploymentList;
        }

        parent::__construct(new TranslatableLabel(
            $message,
            'general.errors.connection.invalidUsernameOrPassword'
        ), $previous);
    }
}
