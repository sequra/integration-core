<?php

namespace SeQura\Core\BusinessLogic\Domain\StoreIntegration\Exceptions;

use SeQura\Core\BusinessLogic\Domain\Translations\Model\BaseTranslatableException;
use SeQura\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
use Throwable;

/**
 * Class CapabilitiesEmptyException.
 *
 * @package SeQura\Core\BusinessLogic\Domain\StoreIntegration\Exceptions
 */
class CapabilitiesEmptyException extends BaseTranslatableException
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
            'Capabilities list is empty.',
            'general.errors.connection.emptyCapabilities'
        ), $previous);
    }
}
