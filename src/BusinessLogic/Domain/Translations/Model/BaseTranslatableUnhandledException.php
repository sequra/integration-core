<?php

namespace SeQura\Core\BusinessLogic\Domain\Translations\Model;

use Throwable;

/**
 * Class BaseTranslatableUnhandledException
 *
 * @package SeQura\Core\BusinessLogic\Domain\Translations\Model
 */
class BaseTranslatableUnhandledException extends BaseTranslatableException
{
    public function __construct(Throwable $previous)
    {
        parent::__construct(
            new TranslatableLabel(
                'Unhandled error occurred: ' . $previous->getMessage(),
                'general.errors.unknown'
            ),
            $previous
        );
    }
}
