<?php

namespace SeQura\Core\BusinessLogic\Providers\QueueNameProvider\Contract;

use SeQura\Core\Infrastructure\TaskExecution\Task;

/**
 * Class QueueNameProviderInterface
 *
 * @package SeQura\Core\BusinessLogic\Utility\QueueNameProvider
 */
interface QueueNameProviderInterface
{
    /**
     * Provides a queue name based on given task.
     *
     * @param Task $task
     *
     * @return string
     */
    public function getQueueName(Task $task): string;
}
