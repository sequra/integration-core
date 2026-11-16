<?php

namespace SeQura\Core\BusinessLogic\Domain\TaskExecution\Services;

use ReflectionClass;
use SeQura\Core\BusinessLogic\Domain\Integration\TaskExecution\BusinessTaskInterface;
use SeQura\Core\BusinessLogic\Domain\TaskExecution\Models\TaskExecutionConfig;
use SeQura\Core\BusinessLogic\Domain\Integration\TaskExecution\TaskMetadataProviderInterface;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\OrderCleanup\Tasks\OrderCleanupTask;

class DefaultTaskMetadataProvider implements TaskMetadataProviderInterface
{
    private const DEFAULT_PRIORITY = 50;

    public function getExecutionConfig(BusinessTaskInterface $task): TaskExecutionConfig
    {
        if ($task instanceof OrderCleanupTask) {
            return new TaskExecutionConfig('order-cleanup', 0, '');
        }

        return new TaskExecutionConfig(
            (new ReflectionClass($task))->getShortName(),
            self::DEFAULT_PRIORITY,
            StoreContext::getInstance()->getStoreId()
        );
    }
}
