<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\GeneralSettings;

use SeQura\Core\BusinessLogic\AdminAPI\GeneralSettings\Requests\GeneralSettingsRequest;
use SeQura\Core\BusinessLogic\AdminAPI\GeneralSettings\Responses\GeneralSettingsResponse;
use SeQura\Core\BusinessLogic\AdminAPI\GeneralSettings\Responses\ShopCategoriesResponse;
use SeQura\Core\BusinessLogic\AdminAPI\GeneralSettings\Responses\SuccessfulGeneralSettingsResponse;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\BadMerchantIdException;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\WrongCredentialsException;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Exceptions\FailedToRetrieveSellingCountriesException;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Exceptions\FailedToRetrieveCategoriesException;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Services\CategoryService;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Services\GeneralSettingsService;
use SeQura\Core\Infrastructure\Http\Exceptions\HttpRequestException;

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
    protected $generalSettingsService;

    /**
     * @var CategoryService
     */
    protected $categoryService;

    /**
     * @param GeneralSettingsService $generalSettingsService
     * @param CategoryService $categoryService
     */
    public function __construct(
        GeneralSettingsService $generalSettingsService,
        CategoryService $categoryService
    ) {
        $this->generalSettingsService = $generalSettingsService;
        $this->categoryService = $categoryService;
    }

    /**
     * Gets active general settings.
     *
     * @return GeneralSettingsResponse
     *
     * @throws BadMerchantIdException
     * @throws WrongCredentialsException
     * @throws FailedToRetrieveSellingCountriesException
     * @throws HttpRequestException
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
}
