<?php

namespace SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Services;

use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Models\CountryConfiguration;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\RepositoryContracts\CountryConfigurationRepositoryInterface;
use SeQura\Core\Infrastructure\ServiceRegister;

/**
 * Class CountryConfigurationService
 *
 * @package SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Services
 */
class CountryConfigurationService
{
    /**
     * Retrieves country configuration from the database via country configuration data repository.
     *
     * @return CountryConfiguration[]|null
     */
    public function getConnectionData(): ?array
    {
        return $this->getCountryConfigurationRepository()->getCountryConfiguration();
    }

    /**
     * Calls the repository to save the country configuration to the database.
     *
     * @param CountryConfiguration[] $countryConfiguration
     *
     * @return void
     */
    public function saveConnectionData(array $countryConfiguration): void
    {
        $this->getCountryConfigurationRepository()->setCountryConfiguration($countryConfiguration);
    }

    /**
     * Returns an instance of the country configuration repository.
     *
     * @return CountryConfigurationRepositoryInterface
     */
    private function getCountryConfigurationRepository(): CountryConfigurationRepositoryInterface
    {
        return ServiceRegister::getService(CountryConfigurationRepositoryInterface::class);
    }
}
