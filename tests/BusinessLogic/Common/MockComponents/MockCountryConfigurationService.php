<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Models\CountryConfiguration;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Services\CountryConfigurationService;

/**
 * Class MockCountryConfigurationService.
 *
 * @package SeQura\Core\Tests\BusinessLogic\Common\MockComponents
 */
class MockCountryConfigurationService extends CountryConfigurationService
{
    /**
     * @var CountryConfiguration[]
     */
    private $countriesConfiguration;

    public function getCountryConfiguration(): ?array
    {
        return $this->countriesConfiguration;
    }

    /**
     * @param array $countryConfiguration
     *
     * @return void
     */
    public function saveCountryConfiguration(array $countryConfiguration): void
    {
        $this->countriesConfiguration = $countryConfiguration;
    }
}
