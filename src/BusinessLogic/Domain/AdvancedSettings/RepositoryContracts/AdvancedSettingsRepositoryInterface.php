<?php

namespace SeQura\Core\BusinessLogic\Domain\AdvancedSettings\RepositoryContracts;

use SeQura\Core\BusinessLogic\Domain\AdvancedSettings\Models\AdvancedSettings;

/**
 * Interface AdvancedSettingsRepositoryInterface.
 *
 * @package SeQura\Core\BusinessLogic\Domain\AdvancedSettings\RepositoryContracts
 */
interface AdvancedSettingsRepositoryInterface
{
    /**
     * @return ?AdvancedSettings
     */
    public function getAdvancedSettings(): ?AdvancedSettings;

    /**
     * @param AdvancedSettings $settings
     *
     * @return void
     */
    public function setAdvancedSettings(AdvancedSettings $settings): void;

    /**
     * @return void
     */
    public function deleteAdvancedSettings(): void;
}
