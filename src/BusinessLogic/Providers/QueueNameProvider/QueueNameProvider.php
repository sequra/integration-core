<?php

namespace SeQura\Core\BusinessLogic\Providers\QueueNameProvider;

use SeQura\Core\BusinessLogic\Providers\QueueNameProvider\Contract\QueueNameProviderInterface;
use SeQura\Core\Infrastructure\TaskExecution\Task;

/**
 * Class QueueNameProvider
 *
 * @package SeQura\Core\BusinessLogic\Providers\QueueNameProvider
 */
class QueueNameProvider implements QueueNameProviderInterface
{
    /**
     * @inheritDoc
     */
    public function getQueueName(Task $task): string
    {
        return $task::getClassName();
    }
}
