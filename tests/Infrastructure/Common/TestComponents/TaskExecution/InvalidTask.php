<?php

namespace SeQura\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution;

use SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException;
use SeQura\Core\Infrastructure\TaskExecution\Task;

/**
 * Class InvalidTask.
 *
 * @package SeQura\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution
 */
class InvalidTask extends Task
{
    public function execute()
    {
    }

    /**
     * @inheritdoc
     * @throws QueueItemDeserializationException
     */
    public function unserialize($serialized)
    {
        throw new QueueItemDeserializationException("Failed to deserialize task.");
    }

    /**
     * @inheritDoc
     * @throws QueueItemDeserializationException
     */
    public static function fromArray(array $array)
    {
        throw new QueueItemDeserializationException("Failed to deserialize task.");
    }

    public function __serialize()
    {
        return $this->toArray();
    }

    /**
     * @param $data
     * @return mixed
     * @throws QueueItemDeserializationException
     */
    public function __unserialize($data)
    {
        throw new QueueItemDeserializationException("Failed to deserialize task.");
    }
}
