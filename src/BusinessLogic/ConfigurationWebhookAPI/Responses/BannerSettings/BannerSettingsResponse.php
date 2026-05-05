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
     * @param BannerSettings $settings
     */
    public function __construct(BannerSettings $settings)
    {
        $this->settings = $settings;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return !$this->settings ? [] : $this->settings->toArray();
    }
}
