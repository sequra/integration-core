<?php

namespace SeQura\Core\BusinessLogic\Domain\OrderCleanup\RepositoryContracts;

/**
 * Interface OrderCleanupScheduleRepositoryInterface
 *
 * Stores when the order cleanup runs next. The schedule is global, not store-scoped.
 *
 * @package SeQura\Core\BusinessLogic\Domain\OrderCleanup\RepositoryContracts
 */
interface OrderCleanupScheduleRepositoryInterface
{
    /**
     * Returns the timestamp of the next cleanup run, or null when the cleanup was never scheduled.
     *
     * @return int|null
     */
    public function getNextRun(): ?int;

    /**
     * Stores the timestamp of the next cleanup run.
     *
     * @param int $nextRun
     *
     * @return void
     */
    public function setNextRun(int $nextRun): void;
}
