<?php

namespace SeQura\Core\BusinessLogic\TaskExecution\Standalone;

use Exception;
use InvalidArgumentException;
use ReflectionClass;
use SeQura\Core\BusinessLogic\Domain\Integration\TaskExecution\BusinessTaskInterface;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\Infrastructure\Logger\Logger;
use SeQura\Core\Infrastructure\Serializer\Interfaces\Serializable;
use SeQura\Core\Infrastructure\Serializer\Serializer;
use SeQura\Core\Infrastructure\TaskExecution\Task;

/**
 * @phpstan-consistent-constructor
 */
class TaskAdapter extends Task
{
    /**
     * @var BusinessTaskInterface
     */
    private $job;

    /**
     * @var string
     */
    private $context;

    public function __construct(BusinessTaskInterface $job, string $context)
    {
        parent::__construct();
        $this->job = $job;
        $this->context = $context;
    }

    /**
     * @param array<string, mixed> $array
     *
     * @throws InvalidArgumentException When the stored entry is not a business job.
     */
    public static function fromArray(array $array): Serializable
    {
        return self::build($array);
    }

    /**
     * @param array<string, mixed> $array
     *
     * @return static
     *
     * @throws InvalidArgumentException When the stored entry is not a business job.
     */
    private static function build(array $array): self
    {
        $taskClass = $array['task_class'] ?? null;
        if (
            !\is_string($taskClass)
            || !class_exists($taskClass)
            || !is_subclass_of($taskClass, BusinessTaskInterface::class)
            || !isset($array['task_data'], $array['context'])
            || !\is_array($array['task_data'])
            || !\is_string($array['context'])
        ) {
            throw new InvalidArgumentException('Stored entry is not a valid business job.');
        }

        return new static($taskClass::fromArray($array['task_data']), $array['context']);
    }

    /**
     * @throws Exception
     */
    public function execute(): void
    {
        StoreContext::doWithStore($this->context, function () {
            $signals = $this->job->execute();
            if ($signals === null) {
                return;
            }

            foreach ($signals as $signal) {
                $this->handleSignal($signal);
            }
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'task_class' => \get_class($this->job),
            'task_data' => $this->job->toArray(),
            'context' => $this->context,
        ];
    }

    public function getType(): string
    {
        return (new ReflectionClass($this->job))->getShortName();
    }

    /**
     * @return array<string, mixed>
     */
    public function __serialize(): array
    {
        return $this->toArray();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function __unserialize($data): void
    {
        $this->restore($data);
    }

    public function serialize(): string
    {
        return Serializer::serialize($this->toArray());
    }

    /**
     * @param string $data
     */
    public function unserialize($data): void
    {
        $this->restore(Serializer::unserialize($data));
    }

    /**
     * @param array<string, mixed> $data
     */
    private function restore(array $data): void
    {
        $adapter = self::build($data);
        $this->job = $adapter->job;
        $this->context = $adapter->context;
    }

    /**
     * @param mixed $signal
     */
    private function handleSignal($signal): void
    {
        if (\is_int($signal) || \is_float($signal)) {
            $this->reportProgress($signal);

            return;
        }

        $this->reportAlive();

        if (\is_string($signal)) {
            Logger::logDebug(sprintf('%s: %s', $this->getType(), $signal));
        }
    }
}
