<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\CountryConfiguration;

use SeQura\Core\BusinessLogic\AdminAPI\CountryConfiguration\Requests\CountryConfigurationRequest;
use SeQura\Core\BusinessLogic\AdminAPI\CountryConfiguration\Responses\CountryConfigurationResponse;
use SeQura\Core\BusinessLogic\AdminAPI\CountryConfiguration\Responses\SellingCountriesResponse;
use SeQura\Core\BusinessLogic\AdminAPI\CountryConfiguration\Responses\SuccessfulCountryConfigurationResponse;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Exceptions\EmptyCountryConfigurationParameterException;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Exceptions\FailedToRetrieveSellingCountriesException;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Exceptions\InvalidCountryCodeForConfigurationException;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Services\CountryConfigurationService;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Services\SellingCountriesService;

/**
 * Class CountryConfigurationController
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\CountryConfiguration
 */
class CountryConfigurationController
{
    /**
     * @var CountryConfigurationService
     */
    protected $countryConfigurationService;

    /**
     * @var SellingCountriesService
     */
    protected $sellingCountriesService;

    /**
     * @param CountryConfigurationService $countryConfigurationService
     * @param SellingCountriesService $sellingCountriesService
     */
    public function __construct(
        CountryConfigurationService $countryConfigurationService,
        SellingCountriesService $sellingCountriesService
    ) {
        $this->countryConfigurationService = $countryConfigurationService;
        $this->sellingCountriesService = $sellingCountriesService;
    }

    /**
     * Gets all the available selling countries.
     *
     * @return SellingCountriesResponse
     *
     * @throws FailedToRetrieveSellingCountriesException
     */
    public function getSellingCountries(): SellingCountriesResponse
    {
        return new SellingCountriesResponse($this->sellingCountriesService->getSellingCountries());
    }

    /**
     * Gets active country configuration.
     *
     * @return CountryConfigurationResponse
     */
    public function getCountryConfigurations(): CountryConfigurationResponse
    {
        return new CountryConfigurationResponse($this->countryConfigurationService->getCountryConfiguration());
    }

    /**
     * Saves a new country configuration.
     *
     * @param CountryConfigurationRequest $request
     *
     * @return SuccessfulCountryConfigurationResponse
     *
     * @throws EmptyCountryConfigurationParameterException
     * @throws InvalidCountryCodeForConfigurationException
     */
    public function saveCountryConfigurations(CountryConfigurationRequest $request): SuccessfulCountryConfigurationResponse
    {
        $this->countryConfigurationService->saveCountryConfiguration($request->transformToDomainModel());

        return new SuccessfulCountryConfigurationResponse();
    }
}
