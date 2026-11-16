<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\TaskExecution\Doubles;

use SeQura\Core\BusinessLogic\Domain\Integration\TaskExecution\BusinessTaskInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\TaskExecution\TaskMetadataProviderInterface;
use SeQura\Core\BusinessLogic\Domain\TaskExecution\Models\TaskExecutionConfig;

class FixedTaskMetadataProvider implements TaskMetadataProviderInterface
{
    public function getExecutionConfig(BusinessTaskInterface $task): TaskExecutionConfig
    {
        return new TaskExecutionConfig('default', 50, '');
    }
}
