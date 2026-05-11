<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\BannerSettings\Requests;

use SeQura\Core\BusinessLogic\Domain\BannerSettings\Models\Banner;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Models\BannerSettings;

/**
 * Trait SaveBannerSettingsRequest
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\BannerSettings\Requests
 */
trait SaveBannerSettingsRequest
{
    /**
     * @var array<int,array<string,string>>
     */
    protected $bannerConfigs;

    /**
     * @param array<int,array<string,string>> $bannerConfigs
     */
    public function __construct(
        array $bannerConfigs = []
    ) {
        $this->bannerConfigs = $bannerConfigs;
    }

    /**
     * Transforms the request to a BannerSettings object.
     *
     * @return BannerSettings
     */
    public function transformToDomainModel(): object
    {
        $arrayOfBannerConfigs = [];
        foreach ($this->bannerConfigs as $bannerConfig) {
            $arrayOfBannerConfigs[] = new Banner(
                $bannerConfig['country'] ?? '',
                $bannerConfig['linkUrl'] ?? '',
                '',
                $bannerConfig['displayLocation'] ?? '',
                isset($bannerConfig['imageBase64']) && $bannerConfig['imageBase64'] !== ''
                    ? $bannerConfig['imageBase64']
                    : null
            );
        }

        return new BannerSettings(
            $arrayOfBannerConfigs
        );
    }
}
