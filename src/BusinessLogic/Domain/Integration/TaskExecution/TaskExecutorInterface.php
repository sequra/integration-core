<?php

namespace SeQura\Core\BusinessLogic\Domain\Integration\TaskExecution;

/**
 * @package SeQura\Core\BusinessLogic\Domain\Integration\TaskExecution
 */
interface TaskExecutorInterface
{
    /**
     * @param BusinessTaskInterface $task
     *
     * @return void
     */
    public function enqueue(BusinessTaskInterface $task): void;

    /**
     * @param BusinessTaskInterface $task
     * @param int $delaySeconds
     *
     * @return void
     */
    public function scheduleDelayed(BusinessTaskInterface $task, int $delaySeconds): void;
}
