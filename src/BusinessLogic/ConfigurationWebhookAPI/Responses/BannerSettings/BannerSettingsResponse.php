<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\BannerSettings;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Models\BannerSettings;

/**
 * Class BannerSettingsResponse
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\BannerSettings
 */
class BannerSettingsResponse extends Response
{
    /**
     * @var BannerSettings
     */
    protected $settings;

    /**
     * @var string[]
     */
    protected $displayLocations;

    /**
     * @var string[]
     */
    protected $sellingCountries;

    /**
     * @param BannerSettings $settings
     * @param string[] $displayLocations
     * @param string[] $sellingCountries
     */
    public function __construct(BannerSettings $settings, array $displayLocations, array $sellingCountries = [])
    {
        $this->settings = $settings;
        $this->displayLocations = $displayLocations;
        $this->sellingCountries = $sellingCountries;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $settingsArray = $this->settings->toArray();

        return [
            'displayLocations' => $this->displayLocations,
            'sellingCountries' => $this->sellingCountries,
            'bannerConfigs' => $settingsArray['bannerConfigs'] ?? [],
        ];
    }
}
