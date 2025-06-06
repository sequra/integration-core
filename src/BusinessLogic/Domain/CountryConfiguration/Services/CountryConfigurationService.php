<?php

namespace SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Services;

use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Exceptions\FailedToRetrieveSellingCountriesException;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Models\CountryConfiguration;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Models\SellingCountry;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\RepositoryContracts\CountryConfigurationRepositoryInterface;

/**
 * Class CountryConfigurationService
 *
 * @package SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Services
 */
class CountryConfigurationService
{
    /**
     * @var CountryConfigurationRepositoryInterface $countryConfigurationRepository
     */
    protected $countryConfigurationRepository;
    /**
     * @var SellingCountriesService $sellingCountriesService
     */
    protected $sellingCountriesService;

    /**
     * @param CountryConfigurationRepositoryInterface $countryConfigurationRepository
     * @param SellingCountriesService $sellerCountriesService
     */
    public function __construct(
        CountryConfigurationRepositoryInterface $countryConfigurationRepository,
        SellingCountriesService $sellerCountriesService
    ) {
        $this->countryConfigurationRepository = $countryConfigurationRepository;
        $this->sellingCountriesService = $sellerCountriesService;
    }

    /**
     * Retrieves country configuration from the database via country configuration data repository.
     *
     * @return CountryConfiguration[]|null
     *
     * @throws FailedToRetrieveSellingCountriesException
     */
    public function getCountryConfiguration(): ?array
    {
        $configuredCountries = $this->countryConfigurationRepository->getCountryConfiguration();
        $sellingCountries = $this->getSellingCountriesCodes();

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

    /**
     * @return string[]
     *
     * @throws FailedToRetrieveSellingCountriesException
     */
    private function getSellingCountriesCodes(): array
    {
        $sellingCountries = $this->sellingCountriesService->getSellingCountries();

        return array_map(function (SellingCountry $sellingCountry) {
            return $sellingCountry->getCode();
        }, $sellingCountries);
    }
}
