<?php

namespace SeQura\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution;

use SeQura\Core\Infrastructure\Serializer\Interfaces\Serializable;
use SeQura\Core\Infrastructure\Serializer\Serializer;
use SeQura\Core\Infrastructure\TaskExecution\Interfaces\Runnable;

class FakeRunnable implements Runnable
{
    private $callHistory = array();
    private $testProperty;

    public function __construct($testProperty = null)
    {
        $this->testProperty = $testProperty;
    }

    public function getMethodCallHistory($methodName)
    {
        return !empty($this->callHistory[$methodName]) ? $this->callHistory[$methodName] : array();
    }

    /**
     * @inheritDoc
     */
    public function serialize()
    {
        return Serializer::serialize(array($this->testProperty, $this->callHistory));
    }

    /**
     * @inheritDoc
     */
    public function unserialize($serialized)
    {
        list($this->testProperty, $this->callHistory) = Serializer::unserialize($serialized);
    }

    /**
     * @inheritDoc
     */
    public static function fromArray(array $array): Serializable
    {
        $instance = new self($array['testProperty']);

        $instance->callHistory = $array['callHistory'];

        return $instance;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return array(
            'testProperty' => $this->testProperty,
            'callHistory' => $this->callHistory,
        );
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
        $this->testProperty = $data['testProperty'];
        $this->callHistory = $data['callHistory'];
    }

    /**
     * Starts runnable run logic.
     */
    public function run(): void
    {
        $this->callHistory['run'][] = array();
    }

    /**
     * @return mixed
     */
    public function getTestProperty()
    {
        return $this->testProperty;
    }

    /**
     * @param mixed $testProperty
     */
    public function setTestProperty($testProperty)
    {
        $this->testProperty = $testProperty;
    }
}
