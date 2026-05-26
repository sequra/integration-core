<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use SeQura\Core\BusinessLogic\Domain\BannerSettings\Models\BannerSettings;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\RepositoryContracts\BannerSettingsRepositoryInterface;

/**
 * Class MockBannerSettingsRepository.
 *
 * @package SeQura\Core\Tests\BusinessLogic\Common\MockComponents
 */
class MockBannerSettingsRepository implements BannerSettingsRepositoryInterface
{
    /**
     * @var ?BannerSettings $settings
     */
    private $settings;

    public function setBannerSettings(BannerSettings $settings): void
    {
        $this->settings = $settings;
    }

    public function getBannerSettings(): ?BannerSettings
    {
        return $this->settings;
    }

    public function deleteBannerSettings(): void
    {
        $this->settings = null;
    }
}
