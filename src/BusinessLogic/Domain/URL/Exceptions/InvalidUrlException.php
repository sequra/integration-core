<?php

namespace SeQura\Core\BusinessLogic\Domain\URL\Exceptions;

use SeQura\Core\BusinessLogic\Domain\Translations\Model\BaseTranslatableException;
use SeQura\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
use Throwable;

/**
 * Class InvalidUrlException.
 *
 * @package SeQura\Core\BusinessLogic\Domain\StoreIntegration\Exceptions
 */
class InvalidUrlException extends BaseTranslatableException
{
    /**
     * @var int
     */
    protected $code = 404;

    /**
     * @param ?Throwable $previous
     */
    public function __construct(?Throwable $previous = null)
    {
        parent::__construct(new TranslatableLabel(
            'Invalid URL.',
            'general.errors.URL.invalidUrl'
        ), $previous);
    }
}
