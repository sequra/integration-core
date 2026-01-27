<?php

namespace SeQura\Core\BusinessLogic\Domain\Integration\AdvancedSettings;

/**
 * Interface AdvancedSettingsServiceInterface
 *
 * @package SeQura\Core\BusinessLogic\Domain\Integration\AdvancedSettings
 */
interface AdvancedSettingsServiceInterface
{
    /**
     * Gets the advanced settings (logging configuration).
     *
     * @return array{isEnabled: bool, level: int}
     */
    public function getAdvancedSettings(): array;

    /**
     * Saves the advanced settings (logging configuration).
     *
     * @param bool $isEnabled
     * @param int $level
     *
     * @return void
     */
    public function saveAdvancedSettings(bool $isEnabled, int $level): void;
}
