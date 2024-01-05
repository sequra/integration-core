<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\GeneralSettings;

use SeQura\Core\BusinessLogic\AdminAPI\GeneralSettings\Requests\GeneralSettingsRequest;
use SeQura\Core\BusinessLogic\AdminAPI\GeneralSettings\Responses\GeneralSettingsResponse;
use SeQura\Core\BusinessLogic\AdminAPI\GeneralSettings\Responses\ShopCategoriesResponse;
use SeQura\Core\BusinessLogic\AdminAPI\GeneralSettings\Responses\ShopPaymentMethodsResponse;
use SeQura\Core\BusinessLogic\AdminAPI\GeneralSettings\Responses\SuccessfulGeneralSettingsResponse;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Exceptions\FailedToRetrieveCategoriesException;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Exceptions\FailedToRetrieveShopPaymentMethodsException;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Services\CategoryService;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Services\GeneralSettingsService;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Services\ShopPaymentMethodService;

/**
 * Class GeneralSettingsController
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\GeneralSettings
 */
class GeneralSettingsController
{
    /**
     * @var GeneralSettingsService
     */
    private $generalSettingsService;

    /**
     * @var CategoryService
     */
    private $categoryService;

    /**
     * @var ShopPaymentMethodService
     */
    private $shopPaymentMethodService;

    /**
     * @param GeneralSettingsService $generalSettingsService
     * @param CategoryService $categoryService
     * @param ShopPaymentMethodService $shopPaymentMethodService
     */
    public function __construct(
        GeneralSettingsService   $generalSettingsService,
        CategoryService          $categoryService,
        ShopPaymentMethodService $shopPaymentMethodService
    )
    {
        $this->generalSettingsService = $generalSettingsService;
        $this->categoryService = $categoryService;
        $this->shopPaymentMethodService = $shopPaymentMethodService;
    }

    /**
     * Gets active general settings.
     *
     * @return GeneralSettingsResponse
     */
    public function getGeneralSettings(): GeneralSettingsResponse
    {
        return new GeneralSettingsResponse($this->generalSettingsService->getGeneralSettings());
    }

    /**
     * Saves new general settings.
     *
     * @param GeneralSettingsRequest $request
     *
     * @return SuccessfulGeneralSettingsResponse
     */
    public function saveGeneralSettings(GeneralSettingsRequest $request): SuccessfulGeneralSettingsResponse
    {
        $this->generalSettingsService->saveGeneralSettings($request->transformToDomainModel());

        return new SuccessfulGeneralSettingsResponse();
    }

    /**
     * Gets shop categories.
     *
     * @return ShopCategoriesResponse
     *
     * @throws FailedToRetrieveCategoriesException
     */
    public function getShopCategories(): ShopCategoriesResponse
    {
        return new ShopCategoriesResponse($this->categoryService->getCategories());
    }

    /**
     * Gets shop payment methods.
     *
     * @return ShopPaymentMethodsResponse
     *
     * @throws FailedToRetrieveShopPaymentMethodsException
     */
    public function getShopPaymentMethods(): ShopPaymentMethodsResponse
    {
        return new ShopPaymentMethodsResponse($this->shopPaymentMethodService->getShopPaymentMethods());
    }
}
