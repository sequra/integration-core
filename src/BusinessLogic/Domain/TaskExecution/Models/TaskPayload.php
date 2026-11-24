<?php

namespace SeQura\Core\BusinessLogic\Domain\TaskExecution\Models;

use SeQura\Core\BusinessLogic\Domain\Integration\TaskExecution\BusinessTaskInterface;
use SeQura\Core\BusinessLogic\Domain\TaskExecution\Exceptions\InvalidTaskPayloadException;
use Throwable;

/**
 * @package SeQura\Core\BusinessLogic\Domain\TaskExecution\Models
 */
class TaskPayload
{
    /**
     * @var string
     */
    private $taskClass;

    /**
     * @var array<string, mixed>
     */
    private $taskData;

    /**
     * @var string
     */
    private $context;

    /**
     * @param string $taskClass
     * @param array<string, mixed> $taskData
     * @param string $context
     */
    private function __construct(string $taskClass, array $taskData, string $context)
    {
        $this->taskClass = $taskClass;
        $this->taskData = $taskData;
        $this->context = $context;
    }

    /**
     * @param BusinessTaskInterface $task
     * @param string $context
     *
     * @return TaskPayload
     */
    public static function fromTask(BusinessTaskInterface $task, string $context): TaskPayload
    {
        return new self(\get_class($task), $task->toArray(), $context);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return TaskPayload
     *
     * @throws InvalidTaskPayloadException
     */
    public static function fromArray(array $data): TaskPayload
    {
        if (
            !isset($data['task_class'], $data['task_data'], $data['context'])
            || !\is_string($data['task_class'])
            || !\is_array($data['task_data'])
            || !\is_string($data['context'])
            || !class_exists($data['task_class'])
            || !is_subclass_of($data['task_class'], BusinessTaskInterface::class)
        ) {
            throw new InvalidTaskPayloadException();
        }

        return new self($data['task_class'], $data['task_data'], $data['context']);
    }

    /**
     * @return BusinessTaskInterface
     *
     * @throws InvalidTaskPayloadException
     */
    public function createTask(): BusinessTaskInterface
    {
        $taskClass = $this->taskClass;

        try {
            return $taskClass::fromArray($this->taskData);
        } catch (Throwable $e) {
            throw new InvalidTaskPayloadException($e);
        }
    }

    /**
     * @return string
     */
    public function getContext(): string
    {
        return $this->context;
    }

    /**
     * @return array{task_class: string, task_data: array<string, mixed>, context: string}
     */
    public function toArray(): array
    {
        return [
            'task_class' => $this->taskClass,
            'task_data' => $this->taskData,
            'context' => $this->context,
        ];
    }
}
