<?php

namespace SeQura\Core\BusinessLogic\Domain\TaskExecution\Services;

use ReflectionClass;
use SeQura\Core\BusinessLogic\Domain\Integration\TaskExecution\BusinessTaskInterface;
use SeQura\Core\BusinessLogic\Domain\TaskExecution\Models\TaskExecutionConfig;
use SeQura\Core\BusinessLogic\Domain\Integration\TaskExecution\TaskMetadataProviderInterface;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;

class DefaultTaskMetadataProvider implements TaskMetadataProviderInterface
{
    private const DEFAULT_PRIORITY = 50;

    public function getExecutionConfig(BusinessTaskInterface $task): TaskExecutionConfig
    {
        return new TaskExecutionConfig(
            (new ReflectionClass($task))->getShortName(),
            self::DEFAULT_PRIORITY,
            StoreContext::getInstance()->getStoreId()
        );
    }
}
