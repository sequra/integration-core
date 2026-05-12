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
     * @param BannerSettings $settings
     * @param string[] $displayLocations
     */
    public function __construct(BannerSettings $settings, array $displayLocations)
    {
        $this->settings = $settings;
        $this->displayLocations = $displayLocations;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $settingsArray = $this->settings->toArray();

        return [
            'displayLocations' => $this->displayLocations,
            'bannerConfigs' => $settingsArray['bannerConfigs'] ?? [],
        ];
    }
}
