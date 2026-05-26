<?php

namespace SeQura\Core\BusinessLogic\Domain\BannerSettings\RepositoryContracts;

use SeQura\Core\BusinessLogic\Domain\BannerSettings\Models\BannerSettings;

/**
 * Interface BannerSettingsRepositoryInterface
 *
 * @package SeQura\Core\BusinessLogic\Domain\BannerSettings\RepositoryContracts
 */
interface BannerSettingsRepositoryInterface
{
    /**
     * Sets banner settings.
     *
     * @param BannerSettings $settings
     *
     * @return void
     */
    public function setBannerSettings(BannerSettings $settings): void;

    /**
     * Retrieves banner settings.
     *
     * @return BannerSettings|null
     */
    public function getBannerSettings(): ?BannerSettings;

    /**
     * Deletes banner settings.
     *
     * @return void
     */
    public function deleteBannerSettings(): void;
}
