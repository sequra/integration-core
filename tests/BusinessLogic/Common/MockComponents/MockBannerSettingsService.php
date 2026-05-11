<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use SeQura\Core\BusinessLogic\Domain\BannerSettings\Models\Banner;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Models\BannerSettings;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\RepositoryContracts\BannerSettingsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Services\BannerSettingsService;

/**
 * Class MockBannerSettingsService
 *
 * @package SeQura\Core\Tests\BusinessLogic\Common\MockComponents
 */
class MockBannerSettingsService extends BannerSettingsService
{
    /** @var BannerSettings */
    protected $bannerSettings;

    /**
     * @param BannerSettingsRepositoryInterface $bannerSettingsRepository
     */
    public function __construct(BannerSettingsRepositoryInterface $bannerSettingsRepository)
    {
        parent::__construct($bannerSettingsRepository, new MockBannerService());
    }

    /**
     * @inheritDoc
     */
    public function getBannerSettings(): ?BannerSettings
    {
        return $this->bannerSettings;
    }

    /**
     * @inheritDoc
     */
    public function getBannerData(string $country, string $displayLocation): ?Banner
    {
        if ($this->bannerSettings === null) {
            return null;
        }

        foreach ($this->bannerSettings->getBannerConfigs() as $bannerConfig) {
            if ($bannerConfig->getCountry() === $country && $bannerConfig->getDisplayLocation() === $displayLocation) {
                return $bannerConfig;
            }
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function setBannerSettings(BannerSettings $bannerSettings): BannerSettings
    {
        foreach ($bannerSettings->getBannerConfigs() as $bannerConfig) {
            $this->assertValidUrl($bannerConfig->getLinkUrl());
        }

        $this->bannerSettings = $bannerSettings;

        return $bannerSettings;
    }
}
