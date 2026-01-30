<?php

namespace SeQura\Core\BusinessLogic\Domain\Integration\Log;

use SeQura\Core\BusinessLogic\Domain\Log\Model\Log;

/**
 * Interface LogServiceInterface
 *
 * @package SeQura\Core\BusinessLogic\Domain\Integration\Log
 */
interface LogServiceInterface
{
    /**
     * Gets the log model.
     *
     * @return Log
     */
    public function getLog(): Log;

    /**
     * Removes/clears all log content.
     *
     * @return void
     */
    public function removeLog(): void;
}
