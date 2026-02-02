<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Requests\GeneralSettings;

use SeQura\Core\BusinessLogic\AdminAPI\GeneralSettings\Requests\SaveGeneralSettingsRequestTrait;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Requests\ConfigurationWebhookRequest;

/**
 * Class SaveGeneralSettingsRequest.
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Requests\Widget
 */
class SaveGeneralSettingsRequest extends ConfigurationWebhookRequest
{
    use SaveGeneralSettingsRequestTrait;

    /**
     * @param mixed[] $payload
     *
     * @return self
     */
    public static function fromPayload(array $payload): object
    {
        return new self(
            $payload['sendOrderReportsPeriodicallyToSeQura'] ?? false,
            $payload['showSeQuraCheckoutAsHostedPage'] ?? false,
            $payload['allowedIPAddresses'] ?? [],
            $payload['excludedProducts'] ?? [],
            $payload['excludedCategories'] ?? [],
            $payload['defaultServicesEndDate'] ?? null,
            $payload['enabledForServices'] ?? [],
            $payload['allowFirstServicePaymentDelay'] ?? [],
            $payload['allowServiceRegistrationItems'] ?? []
        );
    }
}
