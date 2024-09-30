<?php

namespace SeQura\Core\Infrastructure\AutoTest;

use SeQura\Core\Infrastructure\Logger\LogContextData;
use SeQura\Core\Infrastructure\Logger\Logger;
use SeQura\Core\Infrastructure\Serializer\Interfaces\Serializable;
use SeQura\Core\Infrastructure\Serializer\Serializer;
use SeQura\Core\Infrastructure\TaskExecution\Task;

/**
 * Class AutoTestTask.
 *
 * @package SeQura\Core\Infrastructure\AutoTest
 */
/** @phpstan-consistent-constructor */
class AutoTestTask extends Task
{
    /**
     * Dummy data for the task.
     *
     * @var string
     */
    protected $data;

    /**
     * AutoTestTask constructor.
     *
     * @param string $data Dummy data.
     */
    public function __construct($data)
    {
        parent::__construct();
        $this->data = $data;
    }

    /**
     * @inheritDoc
     */
    public static function fromArray(array $array): Serializable
    {
        return new static($array['data']);
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return array('data' => $this->data);
    }

    /**
     * @inheritDoc
     */
    public function __serialize()
    {
        return $this->toArray();
    }

    /**
     * @inheritDoc
     */
    public function __unserialize($data): void
    {
        $this->data = $data['data'];
    }

    /**
     * String representation of object.
     *
     * @return string The string representation of the object or null.
     */
    public function serialize(): ?string
    {
        return Serializer::serialize(array($this->data));
    }

    /**
     * Constructs the object.
     *
     * @param string $serialized <p>
     * The string representation of the object.
     * </p>
     */
    public function unserialize($serialized)
    {
        list($this->data) = Serializer::unserialize($serialized);
    }

    /**
     * Runs task logic.
     */
    public function execute(): void
    {
        $this->reportProgress(5);
        Logger::logInfo('Auto-test task started');

        $this->reportProgress(50);
        Logger::logInfo(
            'Auto-test task parameters',
            'Core',
            [new LogContextData('context', $this->data)]
        );

        $this->reportProgress(100);
        Logger::logInfo('Auto-test task ended');
    }
}
