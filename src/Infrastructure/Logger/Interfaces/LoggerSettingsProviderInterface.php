<?php

namespace SeQura\Core\Infrastructure\Logger\Interfaces;

/**
 * Interface LoggerSettingsProviderInterface.
 *
 * @package SeQura\Core\Infrastructure\Logger\Interfaces
 */
interface LoggerSettingsProviderInterface
{
    const CLASS_NAME = __CLASS__;

    /**
     * @return bool|null
     */
    public function isDefaultLoggerEnabled(): ?bool;

    /**
     * @return int|null
     */
    public function getMinLogLevel(): ?int;

    /**
     * @param int $minLogLevel
     *
     * @return void
     */
    public function saveMinLogLevel(int $minLogLevel): void;
}
