<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers;

use SeQura\Core\Infrastructure\ServiceRegister;

/**
 * Class TopicHandlerRegistry
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers
 */
class TopicHandlerRegistry
{
    /**
     * Map of topic strings to handler class names.
     */
    private const HANDLERS = [
        'get-general-settings' => 'SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\GeneralSettings\GetGeneralSettingsHandler',
        'save-general-settings' => 'SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\GeneralSettings\SaveGeneralSettingsHandler',
        'get-widget-settings' => 'SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\WidgetSettings\GetWidgetSettingsHandler',
        'save-widget-settings' => 'SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\WidgetSettings\SaveWidgetSettingsHandler',
        'get-order-status-list' => 'SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\OrderStatus\GetOrderStatusListHandler',
        'get-order-status-settings' => 'SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\OrderStatus\GetOrderStatusSettingsHandler',
        'save-order-status-settings' => 'SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\OrderStatus\SaveOrderStatusSettingsHandler',
        'get-advanced-settings' => 'SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\AdvancedSettings\GetAdvancedSettingsHandler',
        'save-advanced-settings' => 'SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\AdvancedSettings\SaveAdvancedSettingsHandler',
        'get-log-content' => 'SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\Log\GetLogContentHandler',
        'remove-log-content' => 'SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\Log\RemoveLogContentHandler',
        'get-shop-categories' => 'SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\Shop\GetShopCategoriesHandler',
        'get-shop-products' => 'SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\Shop\GetShopProductsHandler',
        'get-selling-countries' => 'SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\Shop\GetSellingCountriesHandler',
        'get-store-info' => 'SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\Store\GetStoreInfoHandler',
    ];

    /**
     * Gets a handler for the specified topic.
     *
     * @param string $topic
     *
     * @return TopicHandlerInterface|null
     */
    public function getHandler(string $topic): ?TopicHandlerInterface
    {
        $handlerClass = self::HANDLERS[$topic] ?? null;

        if ($handlerClass === null) {
            return null;
        }

        return ServiceRegister::getService($handlerClass);
    }

    /**
     * Gets all registered topic names.
     *
     * @return string[]
     */
    public function getRegisteredTopics(): array
    {
        return array_keys(self::HANDLERS);
    }
}
