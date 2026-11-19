<?php

namespace SeQura\Core\Infrastructure\TaskExecution\Interfaces;

use SeQura\Core\Infrastructure\TaskExecution\QueueItem;
use SeQura\Core\Infrastructure\TaskExecution\Task;

interface StoredTaskUpgrader
{
    /**
     * @param QueueItem $item
     *
     * @return Task|null Null when the stored task is not one this upgrader translates.
     */
    public function upgrade(QueueItem $item): ?Task;
}
