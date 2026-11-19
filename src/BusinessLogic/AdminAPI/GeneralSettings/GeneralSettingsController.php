<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\GeneralSettings;

use SeQura\Core\BusinessLogic\AdminAPI\GeneralSettings\Requests\GeneralSettingsRequest;
use SeQura\Core\BusinessLogic\AdminAPI\GeneralSettings\Requests\StatisticalDataRequest;
use SeQura\Core\BusinessLogic\AdminAPI\GeneralSettings\Responses\GeneralSettingsResponse;
use SeQura\Core\BusinessLogic\AdminAPI\GeneralSettings\Responses\ShopCategoriesResponse;
use SeQura\Core\BusinessLogic\AdminAPI\GeneralSettings\Responses\StatisticalDataResponse;
use SeQura\Core\BusinessLogic\AdminAPI\GeneralSettings\Responses\SuccessfulGeneralSettingsResponse;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\BadMerchantIdException;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\WrongCredentialsException;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Exceptions\FailedToRetrieveSellingCountriesException;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Exceptions\FailedToRetrieveCategoriesException;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Services\CategoryService;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Services\GeneralSettingsService;
use SeQura\Core\BusinessLogic\Domain\StatisticalData\Services\StatisticalDataService;
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
     * @var StatisticalDataService
     */
    protected $statisticalDataService;

    /**
     * @param GeneralSettingsService $generalSettingsService
     * @param CategoryService $categoryService
     * @param StatisticalDataService $statisticalDataService
     */
    public function __construct(
        GeneralSettingsService $generalSettingsService,
        CategoryService $categoryService,
        StatisticalDataService $statisticalDataService
    ) {
        $this->generalSettingsService = $generalSettingsService;
        $this->categoryService = $categoryService;
        $this->statisticalDataService = $statisticalDataService;
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
     * Gets whether seQura may collect statistical data.
     *
     * @return StatisticalDataResponse
     */
    public function getStatisticalData(): StatisticalDataResponse
    {
        return new StatisticalDataResponse($this->statisticalDataService->getStatisticalData());
    }

    /**
     * Saves whether seQura may collect statistical data.
     *
     * @param StatisticalDataRequest $request
     *
     * @return SuccessfulGeneralSettingsResponse
     *
     * @throws \Exception
     */
    public function saveStatisticalData(StatisticalDataRequest $request): SuccessfulGeneralSettingsResponse
    {
        $this->statisticalDataService->saveStatisticalData($request->transformToDomainModel());

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
