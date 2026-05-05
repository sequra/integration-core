<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use SeQura\Core\BusinessLogic\Domain\BannerSettings\Models\Banner;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Models\BannerSettings;
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
     * @inheritDoc
     */
    public function getBannerSettings(): ?BannerSettings
    {
        return $this->bannerSettings;
    }

    /**
     * @inheritDoc
     */
    public function getBannerData(string $country): ?Banner
    {
        return
            new Banner(
                $country,
                'displayOnHomePage',
                'https://www.sequra.com/es/faq#shoppers',
                'https://shop/sequra/es/image.jpg'
            );
    }

    /**
     * @inheritDoc
     */
    public function setBannerSettings(BannerSettings $bannerSettings): void
    {
        foreach ($bannerSettings->getBannerConfigs() as $bannerConfig) {
            $this->assertValidUrl($bannerConfig->getLinkUrl());
            $this->assertValidUrl($bannerConfig->getImageUrl());
        }

        $this->bannerSettings = $bannerSettings;
    }
}
