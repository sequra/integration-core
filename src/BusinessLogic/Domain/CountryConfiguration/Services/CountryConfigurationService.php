<?php

namespace SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Services;

use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Exceptions\EmptyCountryConfigurationParameterException;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Exceptions\FailedToRetrieveSellingCountriesException;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Models\CountryConfiguration;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Models\SellingCountry;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\RepositoryContracts\CountryConfigurationRepositoryInterface;
use SeQura\Core\Infrastructure\Logger\LogContextData;
use SeQura\Core\Infrastructure\Logger\Logger;

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
            if (!\in_array($configuredCountry->getCountryCode(), $sellingCountries)) {
                unset($configuredCountries[$key]);
            }
        }

        return $configuredCountries;
    }

    /**
     * Returns ISO country codes for the configurations currently saved for the store.
     *
     * @return string[]
     *
     * @throws FailedToRetrieveSellingCountriesException
     */
    public function getCountryCodes(): array
    {
        $configurations = $this->getCountryConfiguration() ?? [];

        return array_map(static function (CountryConfiguration $configuration) {
            return $configuration->getCountryCode();
        }, $configurations);
    }

    /**
     * Returns the merchant id configured for the given country, or null when the country has no
     * configuration or is no longer a selling country.
     *
     * Not to be confused with CredentialsService::getMerchantIdByCountryCode(), which resolves the
     * merchant from the stored credentials and throws when it cannot.
     *
     * @param string $countryCode ISO country code.
     *
     * @return string|null
     *
     * @throws FailedToRetrieveSellingCountriesException
     */
    public function getMerchantIdForCountry(string $countryCode): ?string
    {
        $configurations = $this->getCountryConfiguration() ?? [];

        foreach ($configurations as $configuration) {
            if ($configuration->getCountryCode() === $countryCode) {
                return $configuration->getMerchantId();
            }
        }

        return null;
    }

    /**
     * Tells whether a country configuration has been saved for the store.
     *
     * @return bool
     */
    public function isCountryConfigurationSaved(): bool
    {
        return !empty($this->countryConfigurationRepository->getCountryConfiguration());
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
     * @param string[] $countriesCodes
     *
     * @return void
     *
     * @throws FailedToRetrieveSellingCountriesException
     * @throws EmptyCountryConfigurationParameterException
     */
    public function saveCountryConfigurationForCountriesCodes(array $countriesCodes): void
    {
        $sellingCountries = $this->sellingCountriesService->getSellingCountries();

        $countryConfiguration = array_map(function ($countryCode) use ($sellingCountries) {
            foreach ($sellingCountries as $sellingCountry) {
                if ($sellingCountry->getCode() === $countryCode) {
                    return new CountryConfiguration($countryCode, $sellingCountry->getMerchantId());
                }
            }
            return null;
        }, $countriesCodes);
        $countryConfiguration = array_filter($countryConfiguration);

        $this->logCountriesThatCannotBeSoldIn($countriesCodes, $countryConfiguration);

        $this->saveCountryConfiguration($countryConfiguration);
    }

    /**
     * Records the countries that were asked for but cannot be sold in: they either have
     * no merchant of their own or the store does not sell in them. Saving them is not
     * possible, and without a record of it the store looks unconfigured for no reason.
     *
     * @param string[] $countriesCodes
     * @param CountryConfiguration[] $countryConfiguration
     *
     * @return void
     */
    private function logCountriesThatCannotBeSoldIn(array $countriesCodes, array $countryConfiguration): void
    {
        $savedCodes = array_map(static function (CountryConfiguration $configuration) {
            return $configuration->getCountryCode();
        }, $countryConfiguration);

        $skippedCodes = array_diff($countriesCodes, $savedCodes);

        if (empty($skippedCodes)) {
            return;
        }

        Logger::logWarning(
            'Countries were left out of the country configuration: the store has no merchant selling in them.',
            'Core',
            [
                new LogContextData('skippedCountries', implode(',', $skippedCodes)),
                new LogContextData('savedCountries', implode(',', $savedCodes)),
            ]
        );
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
