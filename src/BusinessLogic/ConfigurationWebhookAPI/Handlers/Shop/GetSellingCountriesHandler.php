<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\Shop;

use SeQura\Core\BusinessLogic\AdminAPI\CountryConfiguration\CountryConfigurationController;
use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\TopicHandlerInterface;

/**
 * Class GetSellingCountriesHandler
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\Shop
 */
class GetSellingCountriesHandler implements TopicHandlerInterface
{
    /**
     * @var CountryConfigurationController
     */
    protected $countryConfigurationController;

    /**
     * @param CountryConfigurationController $countryConfigurationController
     */
    public function __construct(CountryConfigurationController $countryConfigurationController)
    {
        $this->countryConfigurationController = $countryConfigurationController;
    }

    /**
     * @inheritDoc
     */
    public function handle(array $payload): Response
    {
        return $this->countryConfigurationController->getSellingCountries();
    }
}
