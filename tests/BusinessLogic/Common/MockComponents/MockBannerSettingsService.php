<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use SeQura\Core\BusinessLogic\Domain\BannerSettings\Exceptions\InvalidBannerUrlException;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Models\Banner;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Models\BannerInput;
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
     * @param BannerInput[] $bannerInputs
     *
     * @return BannerSettings
     *
     * @throws InvalidBannerUrlException
     */
    public function setBannerSettings(array $bannerInputs): BannerSettings
    {
        $banners = [];
        foreach ($bannerInputs as $input) {
            $this->assertValidUrl($input->getLinkUrl());
            $banners[] = new Banner(
                $input->getCountry(),
                $input->getLinkUrl(),
                'https://shop.test/banners/' . $input->getCountry() . '_' . $input->getDisplayLocation() . '.png',
                $input->getDisplayLocation()
            );
        }

        $this->bannerSettings = new BannerSettings($banners);

        return $this->bannerSettings;
    }

    /**
     * @param BannerSettings $bannerSettings
     *
     * @return void
     */
    public function seedBannerSettings(BannerSettings $bannerSettings): void
    {
        $this->bannerSettings = $bannerSettings;
    }
}
