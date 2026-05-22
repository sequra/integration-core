<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\BannerSettings\Responses;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Models\BannerSettings;

/**
 * Class BannerSettingsResponse
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\BannerSettings\Responses
 */
class BannerSettingsResponse extends Response
{
    /**
     * @var BannerSettings
     */
    protected $bannerSettings;

    /**
     * @var string[]
     */
    protected $displayLocations;

    /**
     * @var string[]
     */
    protected $sellingCountries;

    /**
     * @param BannerSettings $bannerSettings
     * @param string[] $displayLocations
     * @param string[] $sellingCountries
     */
    public function __construct(
        BannerSettings $bannerSettings,
        array $displayLocations,
        array $sellingCountries = []
    ) {
        $this->bannerSettings = $bannerSettings;
        $this->displayLocations = $displayLocations;
        $this->sellingCountries = $sellingCountries;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            'displayLocations' => $this->displayLocations,
            'sellingCountries' => $this->sellingCountries,
            'bannerConfigs' => $this->bannerSettings->toArray()['bannerConfigs'],
        ];
    }
}
