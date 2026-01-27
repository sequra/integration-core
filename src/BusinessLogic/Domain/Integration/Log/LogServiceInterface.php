<?php

namespace SeQura\Core\BusinessLogic\Domain\Integration\Log;

/**
 * Interface LogServiceInterface
 *
 * @package SeQura\Core\BusinessLogic\Domain\Integration\Log
 */
interface LogServiceInterface
{
    /**
     * Gets the log content as an array of formatted log entries.
     *
     * @return string[]
     */
    public function getLogContent(): array;

    /**
     * Removes/clears all log content.
     *
     * @return void
     */
    public function removeLogContent(): void;
}
