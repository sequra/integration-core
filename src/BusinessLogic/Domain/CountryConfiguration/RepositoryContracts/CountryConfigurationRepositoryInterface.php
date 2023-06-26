<?php

namespace SeQura\Core\BusinessLogic\Domain\CountryConfiguration\RepositoryContracts;

use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Models\CountryConfiguration;

/**
 * Interface CountryConfigurationRepositoryInterface
 *
 * @package SeQura\Core\BusinessLogic\Domain\CountryConfiguration\RepositoryContracts
 */
interface CountryConfigurationRepositoryInterface
{
    /**
     * Returns an array of CountryConfigurations for current store context.
     *
     * @return CountryConfiguration[]|null
     */
    public function getCountryConfiguration(): ?array;

    /**
     * Insert/update CountryConfiguration for current store context.
     *
     * @param CountryConfiguration[] $countryConfigurations
     *
     * @return void
     */
    public function setCountryConfiguration(array $countryConfigurations): void;
}
