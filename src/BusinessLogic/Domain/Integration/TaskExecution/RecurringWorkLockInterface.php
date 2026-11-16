<?php

namespace SeQura\Core\BusinessLogic\Domain\Integration\TaskExecution;

/**
 * @package SeQura\Core\BusinessLogic\Domain\Integration\TaskExecution
 */
interface RecurringWorkLockInterface
{
    /**
     * @return bool True when the caller may run the recurring work, false when another process holds the lock.
     */
    public function tryAcquire(): bool;

    /**
     * @return void
     */
    public function release(): void;
}
