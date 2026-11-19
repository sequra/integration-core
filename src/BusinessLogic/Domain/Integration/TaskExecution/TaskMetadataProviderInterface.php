<?php

namespace SeQura\Core\BusinessLogic\Domain\Integration\TaskExecution;

use SeQura\Core\BusinessLogic\Domain\TaskExecution\Models\TaskExecutionConfig;

/**
 * @package SeQura\Core\BusinessLogic\Domain\Integration\TaskExecution
 */
interface TaskMetadataProviderInterface
{
    /**
     * @param BusinessTaskInterface $task
     *
     * @return TaskExecutionConfig
     */
    public function getExecutionConfig(BusinessTaskInterface $task): TaskExecutionConfig;
}
