<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\CountryConfiguration;

use SeQura\Core\BusinessLogic\AdminAPI\CountryConfiguration\Requests\CountryConfigurationRequest;
use SeQura\Core\BusinessLogic\AdminAPI\CountryConfiguration\Responses\CountryConfigurationResponse;
use SeQura\Core\BusinessLogic\AdminAPI\CountryConfiguration\Responses\SellingCountriesResponse;
use SeQura\Core\BusinessLogic\AdminAPI\CountryConfiguration\Responses\SuccessfulCountryConfigurationResponse;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Models\CountryConfiguration;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Services\CountryConfigurationService;

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
    private $countryConfigurationService;

    /**
     * @param CountryConfigurationService $countryConfigurationService
     */
    public function __construct(CountryConfigurationService $countryConfigurationService)
    {
        $this->countryConfigurationService = $countryConfigurationService;
    }

    /**
     * Gets all the available selling countries.
     *
     * @return SellingCountriesResponse
     */
    public function getSellingCountries(): SellingCountriesResponse
    {
        return new SellingCountriesResponse();
    }

    /**
     * Gets active country configuration.
     *
     * @return CountryConfigurationResponse
     */
    public function getCountryConfigurations(): CountryConfigurationResponse
    {
        return new CountryConfigurationResponse(
            [
                new CountryConfiguration('CO','logeecom'),
                new CountryConfiguration('ES','logeecom'),
                new CountryConfiguration('FR','logeecom'),
            ]
        );
    }

    /**
     * Saves a new country configuration.
     *
     * @param CountryConfigurationRequest $request
     *
     * @return SuccessfulCountryConfigurationResponse
     */
    public function saveCountryConfigurations(CountryConfigurationRequest $request): SuccessfulCountryConfigurationResponse
    {
        return new SuccessfulCountryConfigurationResponse();
    }
}
