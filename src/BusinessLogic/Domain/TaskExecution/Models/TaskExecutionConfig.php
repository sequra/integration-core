<?php

namespace SeQura\Core\BusinessLogic\Domain\TaskExecution\Models;

use SeQura\Core\BusinessLogic\Domain\TaskExecution\Exceptions\InvalidTaskExecutionConfigException;
use SeQura\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;

/**
 * @package SeQura\Core\BusinessLogic\Domain\TaskExecution\Models
 */
class TaskExecutionConfig
{
    private const MIN_PRIORITY = 0;
    private const MAX_PRIORITY = 100;

    /**
     * @var string
     */
    private $queueName;

    /**
     * @var int
     */
    private $priority;

    /**
     * @var string
     */
    private $context;

    /**
     * @param string $queueName
     * @param int $priority
     * @param string $context
     *
     * @throws InvalidTaskExecutionConfigException
     */
    public function __construct(string $queueName, int $priority, string $context)
    {
        if (trim($queueName) === '') {
            throw new InvalidTaskExecutionConfigException(
                new TranslatableLabel(
                    'Queue name must not be empty.',
                    'general.errors.taskExecution.emptyQueueName'
                )
            );
        }

        if ($priority < self::MIN_PRIORITY || $priority > self::MAX_PRIORITY) {
            throw new InvalidTaskExecutionConfigException(
                new TranslatableLabel(
                    'Priority must be between 0 and 100.',
                    'general.errors.taskExecution.priorityOutOfRange'
                )
            );
        }

        $this->queueName = $queueName;
        $this->priority = $priority;
        $this->context = $context;
    }

    /**
     * @return string
     */
    public function getQueueName(): string
    {
        return $this->queueName;
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * @return string
     */
    public function getContext(): string
    {
        return $this->context;
    }
}
