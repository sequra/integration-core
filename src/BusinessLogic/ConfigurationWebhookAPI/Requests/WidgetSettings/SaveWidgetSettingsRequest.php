<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Requests\WidgetSettings;

use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Requests\ConfigurationWebhookRequest;
use SeQura\Core\BusinessLogic\AdminAPI\PromotionalWidgets\Requests\SaveWidgetSettingsRequest as
    SaveWidgetSettingsRequestTrait;

/**
 * Class SaveWidgetSettingsRequest.
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Requests\WidgetSettings
 */
class SaveWidgetSettingsRequest extends ConfigurationWebhookRequest
{
    use SaveWidgetSettingsRequestTrait;

    /**
     * @param mixed[] $payload
     *
     * @return self
     */
    public static function fromPayload(array $payload): object
    {
        return new self(
            $payload['displayWidgetOnProductPage'] ?? false,
            $payload['showInstallmentAmountInProductListing'] ?? false,
            $payload['showInstallmentAmountInCartPage'] ?? false,
            $payload['widgetStyles'] ?? '',
            $payload['productPriceSelector'] ?? '',
            $payload['defaultProductLocationSelector'] ?? '',
            $payload['cartPriceSelector'] ?? '',
            $payload['cartLocationSelector'] ?? '',
            $payload['widgetOnCartPage'] ?? '',
            $payload['widgetOnListingPage'] ?? '',
            $payload['listingPriceSelector'] ?? '',
            $payload['listingLocationSelector'] ?? '',
            $payload['altProductPriceSelector'] ?? '',
            $payload['altProductPriceTriggerSelector'] ?? '',
            $payload['customLocations'] ?? []
        );
    }
}
