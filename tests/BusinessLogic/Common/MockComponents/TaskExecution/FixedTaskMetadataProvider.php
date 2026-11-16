<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents\TaskExecution;

use SeQura\Core\BusinessLogic\Domain\Integration\TaskExecution\BusinessTaskInterface;
use SeQura\Core\BusinessLogic\Domain\TaskExecution\Models\TaskExecutionConfig;
use SeQura\Core\BusinessLogic\Domain\Integration\TaskExecution\TaskMetadataProviderInterface;

class FixedTaskMetadataProvider implements TaskMetadataProviderInterface
{
    /**
     * @var TaskExecutionConfig
     */
    private $config;

    public function __construct(TaskExecutionConfig $config)
    {
        $this->config = $config;
    }

    public function getExecutionConfig(BusinessTaskInterface $task): TaskExecutionConfig
    {
        return $this->config;
    }
}
