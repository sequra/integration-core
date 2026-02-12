<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\GeneralSettings;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\TopicHandlerInterface;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\GeneralSettings\GetGeneralSettingsResponse;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\SuccessResponse;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\BadMerchantIdException;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\WrongCredentialsException;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Exceptions\FailedToRetrieveSellingCountriesException;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Services\GeneralSettingsService;
use SeQura\Core\BusinessLogic\Domain\Integration\Category\CategoryServiceInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\Product\ProductServiceInterface;
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
     * @param GeneralSettingsService $generalSettingsService
     * @param ProductServiceInterface $productService
     * @param CategoryServiceInterface $categoryService
     */
    public function __construct(
        GeneralSettingsService $generalSettingsService,
        ProductServiceInterface $productService,
        CategoryServiceInterface $categoryService
    ) {
        $this->generalSettingsService = $generalSettingsService;
        $this->productService = $productService;
        $this->categoryService = $categoryService;
    }

    /**
     * @param mixed[] $payload
     *
     * @return GetGeneralSettingsResponse|SuccessResponse
     *
     * @throws BadMerchantIdException
     * @throws FailedToRetrieveSellingCountriesException
     * @throws HttpRequestException
     * @throws WrongCredentialsException
     */
    public function handle(array $payload): Response
    {
        $generalSettings = $this->generalSettingsService->getGeneralSettings();

        if (!$generalSettings) {
            return new SuccessResponse();
        }

        $products = !empty($generalSettings->getExcludedProducts())
            ? $this->productService->getShopProductByIds($generalSettings->getExcludedProducts()) : [];

        $categories = !empty($generalSettings->getExcludedCategories())
            ? $this->categoryService->getCategoriesByIds($generalSettings->getExcludedCategories()) : [];

        return new GetGeneralSettingsResponse($generalSettings, $products, $categories);
    }
}
