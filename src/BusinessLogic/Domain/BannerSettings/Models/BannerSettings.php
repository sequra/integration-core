<?php

namespace SeQura\Core\BusinessLogic\Domain\BannerSettings\Models;

use SeQura\Core\Infrastructure\Data\DataTransferObject;

/**
 * Class BannerSettings
 *
 * @package SeQura\Core\BusinessLogic\Domain\BannerSettings\Models
 */
class BannerSettings extends DataTransferObject
{
    /**
     * @var Banner[] $bannerConfigs
     */
    protected $bannerConfigs;

    /**
     * @param Banner[] $bannerConfigs
     */
    public function __construct(array $bannerConfigs = [])
    {
        $this->bannerConfigs = $bannerConfigs;
    }

    /**
     * @return Banner[]
     */
    public function getBannerConfigs(): array
    {
        return $this->bannerConfigs;
    }

    /**
     * @param Banner[] $bannerConfigs
     */
    public function setBannerConfigs(array $bannerConfigs): void
    {
        $this->bannerConfigs = $bannerConfigs;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $bannerSettingsArray = [];

        foreach ($this->bannerConfigs as $bannerConfig) {
            $bannerSettingsArray['bannerConfigs'][] = [
                'country' => $bannerConfig->getCountry(),
                'linkUrl' => $bannerConfig->getLinkUrl(),
                'imageUrl' => $bannerConfig->getImageUrl(),
                'displayLocation' => $bannerConfig->getDisplayLocation()
            ];
        }

        return $bannerSettingsArray;
    }
}
