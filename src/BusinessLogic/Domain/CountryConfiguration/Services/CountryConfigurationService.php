<?php

namespace SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Services;

use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Models\CountryConfiguration;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\RepositoryContracts\CountryConfigurationRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\SellingCountries\SellingCountriesServiceInterface;

/**
 * Class CountryConfigurationService
 *
 * @package SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Services
 */
class CountryConfigurationService
{
    /**
     * @var CountryConfigurationRepositoryInterface
     */
    protected $countryConfigurationRepository;
    /**
     * @var SellingCountriesServiceInterface
     */
    protected $sellingCountriesService;

    /**
     * @param CountryConfigurationRepositoryInterface $countryConfigurationRepository
     * @param SellingCountriesServiceInterface $sellingCountriesService
     */
    public function __construct(
        CountryConfigurationRepositoryInterface $countryConfigurationRepository,
        SellingCountriesServiceInterface $sellingCountriesService
    ) {
        $this->countryConfigurationRepository = $countryConfigurationRepository;
        $this->sellingCountriesService = $sellingCountriesService;
    }

    /**
     * Retrieves country configuration from the database via country configuration data repository.
     *
     * @return CountryConfiguration[]|null
     */
    public function getCountryConfiguration(): ?array
    {
        $configuredCountries = $this->countryConfigurationRepository->getCountryConfiguration();
        $sellingCountries = $this->sellingCountriesService->getSellingCountries();

        if (empty($configuredCountries)) {
            return null;
        }

        foreach ($configuredCountries as $key => $configuredCountry) {
            if (!in_array($configuredCountry->getCountryCode(), $sellingCountries)) {
                unset($configuredCountries[$key]);
            }
        }

        return $configuredCountries;
    }

    /**
     * Calls the repository to save the country configuration to the database.
     *
     * @param CountryConfiguration[] $countryConfiguration
     *
     * @return void
     */
    public function saveCountryConfiguration(array $countryConfiguration): void
    {
        $this->countryConfigurationRepository->setCountryConfiguration($countryConfiguration);
    }
}
