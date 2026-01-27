<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\Shop;

use SeQura\Core\BusinessLogic\AdminAPI\GeneralSettings\GeneralSettingsController;
use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\TopicHandlerInterface;

/**
 * Class GetShopCategoriesHandler
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\Shop
 */
class GetShopCategoriesHandler implements TopicHandlerInterface
{
    /**
     * @var GeneralSettingsController
     */
    protected $generalSettingsController;

    /**
     * @param GeneralSettingsController $generalSettingsController
     */
    public function __construct(GeneralSettingsController $generalSettingsController)
    {
        $this->generalSettingsController = $generalSettingsController;
    }

    /**
     * @inheritDoc
     */
    public function handle(array $payload): Response
    {
        return $this->generalSettingsController->getShopCategories();
    }
}
