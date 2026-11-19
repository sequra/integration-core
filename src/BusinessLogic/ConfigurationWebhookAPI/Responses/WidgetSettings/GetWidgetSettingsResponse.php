<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\WidgetSettings;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Models\SeQuraPaymentMethod;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\WidgetSettings;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Services\WidgetSettingsService;

/**
 * Class GetWidgetSettingsResponse.
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\WidgetSettings
 */
class GetWidgetSettingsResponse extends Response
{
    /**
     * @var WidgetSettings $widgetSettings
     */
    protected $widgetSettings;

    /**
     * @var SeQuraPaymentMethod[] $paymentMethods
     */
    protected $paymentMethods;

    /**
     * @param ?WidgetSettings $widgetSettings
     * @param SeQuraPaymentMethod[] $paymentMethods
     */
    public function __construct(?WidgetSettings $widgetSettings, array $paymentMethods)
    {
        $this->widgetSettings = $widgetSettings;
        $this->paymentMethods = $paymentMethods;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $response = !$this->widgetSettings ? [] : $this->widgetSettings->toArray();
        $response['paymentMethods'] = array_map(function (SeQuraPaymentMethod $paymentMethod) {
            return [
                'category' => $paymentMethod->getCategory(),
                'product' => $paymentMethod->getProduct(),
                'title' => $paymentMethod->getTitle()
            ];
        }, $this->paymentMethods);
        $response['paymentMethodCategoriesForProductPage'] =
            WidgetSettingsService::WIDGET_SUPPORTED_CATEGORIES_ON_PRODUCT_PAGE;
        $response['paymentMethodCategoriesForCart'] =
            WidgetSettingsService::WIDGET_SUPPORTED_CATEGORIES_ON_CART_PAGE;
        $response['paymentMethodCategoriesForListing'] =
            WidgetSettingsService::MINI_WIDGET_SUPPORTED_CATEGORIES_ON_PRODUCT_LISTING_PAGE;

        return $response;
    }
}
