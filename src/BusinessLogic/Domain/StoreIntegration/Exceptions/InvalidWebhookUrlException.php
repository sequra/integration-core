<?php

namespace SeQura\Core\BusinessLogic\Domain\StoreIntegration\Exceptions;

use SeQura\Core\BusinessLogic\Domain\Translations\Model\BaseTranslatableException;
use SeQura\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
use Throwable;

/**
 * Class InvalidWebhookUrlException.
 *
 * @package SeQura\Core\BusinessLogic\Domain\StoreIntegration\Exceptions
 */
class InvalidWebhookUrlException extends BaseTranslatableException
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
            'Invalid webhook URL.',
            'general.errors.connection.invalidWebhookUrl'
        ), $previous);
    }
}
