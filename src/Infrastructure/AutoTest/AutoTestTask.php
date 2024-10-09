<?php

namespace SeQura\Core\Infrastructure\AutoTest;

use SeQura\Core\Infrastructure\Logger\LogContextData;
use SeQura\Core\Infrastructure\Logger\Logger;
use SeQura\Core\Infrastructure\Serializer\Serializer;
use SeQura\Core\Infrastructure\TaskExecution\Task;

/**
 * Class AutoTestTask.
 *
 * @package SeQura\Core\Infrastructure\AutoTest
 */
/**
 * @phpstan-consistent-constructor
 */
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
    public static function fromArray(array $array)
    {
        return new static($array['data']);
    }

    /**
     * @inheritDoc
     */
    public function toArray()
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
    public function __unserialize($data)
    {
        $this->data = $data['data'];
    }

    /**
     * @inheritDoc
     */
    public function serialize()
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
     * @inheritDoc
     */
    public function execute()
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
