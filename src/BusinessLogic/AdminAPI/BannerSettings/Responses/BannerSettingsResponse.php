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
     * @var BannerSettings|null
     */
    protected $bannerSettings;

    /**
     * @var string[]
     */
    protected $displayLocations;

    /**
     * @param BannerSettings|null $bannerSettings
     * @param string[] $displayLocations
     */
    public function __construct(?BannerSettings $bannerSettings, array $displayLocations)
    {
        $this->bannerSettings = $bannerSettings;
        $this->displayLocations = $displayLocations;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $settingsArray = $this->bannerSettings ? $this->bannerSettings->toArray() : [];

        return [
            'displayLocations' => $this->displayLocations,
            'bannerConfigs' => $settingsArray['bannerConfigs'] ?? [],
        ];
    }
}
