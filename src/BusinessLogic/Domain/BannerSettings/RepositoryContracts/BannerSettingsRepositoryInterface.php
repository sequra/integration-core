<?php

namespace SeQura\Core\BusinessLogic\Domain\BannerSettings\RepositoryContracts;

use Exception;
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
     *
     * @throws Exception
     */
    public function setBannerSettings(BannerSettings $settings): void;

    /**
     * Retrieves banner settings.
     *
     * @return BannerSettings|null
     *
     * @throws Exception
     */
    public function getBannerSettings(): ?BannerSettings;

    /**
     * Deletes banner settings.
     *
     * @return void
     *
     * @throws Exception
     */
    public function deleteBannerSettings(): void;
}
