<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\GeneralSettings;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\TopicHandlerInterface;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\GeneralSettings\GetGeneralSettingsResponse;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\BadMerchantIdException;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\WrongCredentialsException;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Exceptions\FailedToRetrieveSellingCountriesException;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Models\CountryConfiguration;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Services\CountryConfigurationService;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Services\GeneralSettingsService;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Services\OrderIdentifiersService;
use SeQura\Core\BusinessLogic\Domain\Integration\Category\CategoryServiceInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\Product\ProductServiceInterface;
use SeQura\Core\BusinessLogic\Domain\StatisticalData\Services\StatisticalDataService;
use SeQura\Core\Infrastructure\Http\Exceptions\HttpRequestException;

/**
 * Class GetGeneralSettingsHandler
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\GeneralSettings
 */
class GetGeneralSettingsHandler implements TopicHandlerInterface
{
    /**
     * @var GeneralSettingsService $generalSettingsService
     */
    protected $generalSettingsService;
    /**
     * @var ProductServiceInterface $productService
     */
    protected $productService;
    /**
     * @var CategoryServiceInterface $categoryService
     */
    protected $categoryService;

    /**
     * @var CountryConfigurationService $countryConfigurationService
     */
    protected $countryConfigurationService;

    /**
     * @var OrderIdentifiersService $orderIdentifiersService
     */
    protected $orderIdentifiersService;

    /**
     * @var StatisticalDataService $statisticalDataService
     */
    protected $statisticalDataService;

    /**
     * @param GeneralSettingsService $generalSettingsService
     * @param ProductServiceInterface $productService
     * @param CategoryServiceInterface $categoryService
     * @param CountryConfigurationService $countryConfigurationService
     * @param OrderIdentifiersService $orderIdentifiersService
     * @param StatisticalDataService $statisticalDataService
     */
    public function __construct(
        GeneralSettingsService $generalSettingsService,
        ProductServiceInterface $productService,
        CategoryServiceInterface $categoryService,
        CountryConfigurationService $countryConfigurationService,
        OrderIdentifiersService $orderIdentifiersService,
        StatisticalDataService $statisticalDataService
    ) {
        $this->generalSettingsService = $generalSettingsService;
        $this->productService = $productService;
        $this->categoryService = $categoryService;
        $this->countryConfigurationService = $countryConfigurationService;
        $this->orderIdentifiersService = $orderIdentifiersService;
        $this->statisticalDataService = $statisticalDataService;
    }

    /**
     * @param mixed[] $payload
     *
     * @return GetGeneralSettingsResponse
     *
     * @throws BadMerchantIdException
     * @throws FailedToRetrieveSellingCountriesException
     * @throws HttpRequestException
     * @throws WrongCredentialsException
     */
    public function handle(array $payload): Response
    {
        $generalSettings = $this->generalSettingsService->getGeneralSettings();

        $excludedProducts = $generalSettings ? $generalSettings->getExcludedProducts() : [];
        $excludedCategories = $generalSettings ? $generalSettings->getExcludedCategories() : [];

        $products = !empty($excludedProducts) ? $this->productService->getShopProductByIds($excludedProducts) : [];
        $categories = !empty($excludedCategories) ? $this->categoryService->getCategoriesByIds($excludedCategories) : [];

        $countryConfigurations = $this->countryConfigurationService->getCountryConfiguration() ?? [];
        $sellingCountries = array_map(function (CountryConfiguration $cc) {
            return $cc->getCountryCode();
        }, $countryConfigurations);

        $statisticalData = $this->statisticalDataService->getStatisticalData();

        return new GetGeneralSettingsResponse(
            $generalSettings,
            $products,
            $categories,
            $sellingCountries,
            $this->orderIdentifiersService->getAvailableOrderIdentifiers(),
            $statisticalData !== null && $statisticalData->isSendStatisticalData()
        );
    }
}
