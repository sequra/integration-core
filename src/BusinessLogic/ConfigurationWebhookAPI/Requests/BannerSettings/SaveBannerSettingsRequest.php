<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Requests\BannerSettings;

use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Requests\ConfigurationWebhookRequest;
use SeQura\Core\BusinessLogic\AdminAPI\BannerSettings\Requests\SaveBannerSettingsRequest
    as SaveBannerSettingsRequestTrait;

/**
 * Class SaveBannerSettingsRequest
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Requests\BannerSettings
 */
class SaveBannerSettingsRequest extends ConfigurationWebhookRequest
{
    use SaveBannerSettingsRequestTrait;

    public static function fromPayload(array $payload): object
    {
        return new self(
            $payload['bannerConfigs'] ?? []
        );
    }
}
