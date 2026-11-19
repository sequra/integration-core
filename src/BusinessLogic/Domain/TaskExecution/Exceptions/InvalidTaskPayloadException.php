<?php

namespace SeQura\Core\BusinessLogic\Domain\TaskExecution\Exceptions;

use SeQura\Core\BusinessLogic\Domain\Translations\Model\BaseTranslatableException;
use SeQura\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
use Throwable;

/**
 * @package SeQura\Core\BusinessLogic\Domain\TaskExecution\Exceptions
 */
class InvalidTaskPayloadException extends BaseTranslatableException
{
    /**
     * @var int
     */
    protected $code = 400;

    /**
     * @param ?Throwable $previous
     */
    public function __construct(?Throwable $previous = null)
    {
        parent::__construct(new TranslatableLabel(
            'Invalid task payload.',
            'general.errors.taskExecution.invalidTaskPayload'
        ), $previous);
    }
}
