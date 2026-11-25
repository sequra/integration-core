<?php

namespace SeQura\Core\BusinessLogic\Domain\TaskExecution\Exceptions;

use SeQura\Core\BusinessLogic\Domain\Translations\Model\BaseTranslatableException;
use SeQura\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
use Throwable;

/**
 * @package SeQura\Core\BusinessLogic\Domain\TaskExecution\Exceptions
 */
class InvalidTaskExecutionConfigException extends BaseTranslatableException
{
    /**
     * @var int
     */
    protected $code = 400;

    /**
     * @param TranslatableLabel $translatableLabel
     * @param ?Throwable $previous
     */
    public function __construct(TranslatableLabel $translatableLabel, ?Throwable $previous = null)
    {
        parent::__construct($translatableLabel, $previous);
    }
}
