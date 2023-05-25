<?php

namespace SeQura\Core\Infrastructure\Logger\Interfaces;

use SeQura\Core\Infrastructure\Logger\LogData;

/**
 * Interface LoggerAdapter.
 *
 * @package SeQura\Core\Infrastructure\Logger\Interfaces
 */
interface LoggerAdapter
{
    /**
     * Log message in system
     *
     * @param LogData $data
     */
    public function logMessage(LogData $data);
}
