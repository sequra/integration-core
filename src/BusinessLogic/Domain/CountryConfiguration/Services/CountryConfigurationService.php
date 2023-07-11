<?php

namespace SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Services;

use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Models\CountryConfiguration;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\RepositoryContracts\CountryConfigurationRepositoryInterface;

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
    private $countryConfigurationRepository;

    /**
     * @param CountryConfigurationRepositoryInterface $countryConfigurationRepository
     */
    public function __construct(CountryConfigurationRepositoryInterface $countryConfigurationRepository)
    {
        $this->countryConfigurationRepository = $countryConfigurationRepository;
    }

    /**
     * Retrieves country configuration from the database via country configuration data repository.
     *
     * @return CountryConfiguration[]|null
     */
    public function getCountryConfiguration(): ?array
    {
        return $this->countryConfigurationRepository->getCountryConfiguration();
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
