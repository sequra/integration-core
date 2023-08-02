<?php

namespace SeQura\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution;

use SeQura\Core\Infrastructure\TaskExecution\Exceptions\AbortTaskExecutionException;
use SeQura\Core\Infrastructure\TaskExecution\Task;

/**
 * Class AbortTask.
 *
 * @package SeQura\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution
 */
class AbortTask extends Task
{
    public function execute()
    {
        throw new AbortTaskExecutionException('Abort mission!');
    }

    /**
     * @inheritDoc
     */
    public static function fromArray(array $array)
    {
        return new static();
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return array();
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
    }
}
