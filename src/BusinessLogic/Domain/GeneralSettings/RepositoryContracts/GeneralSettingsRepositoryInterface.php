<?php

namespace SeQura\Core\BusinessLogic\Domain\GeneralSettings\RepositoryContracts;

use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Models\GeneralSettings;

/**
 * Interface GeneralSettingsRepositoryInterface
 *
 * @package SeQura\Core\BusinessLogic\Domain\GeneralSettings\RepositoryContracts
 */
interface GeneralSettingsRepositoryInterface
{
    /**
     * Returns GeneralSettings for current store context.
     *
     * @return GeneralSettings|null
     */
    public function getGeneralSettings(): ?GeneralSettings;

    /**
     * Insert/update GeneralSettings for current store context.
     *
     * @param GeneralSettings $generalSettings
     *
     * @return void
     */
    public function setGeneralSettings(GeneralSettings $generalSettings): void;
}
