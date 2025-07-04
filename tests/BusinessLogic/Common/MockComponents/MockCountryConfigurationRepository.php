<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use SeQura\Core\BusinessLogic\DataAccess\CountryConfiguration\Entities\CountryConfiguration;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\RepositoryContracts\CountryConfigurationRepositoryInterface;

/**
 * Class MockCountryConfigurationRepository.
 *
 * @package SeQura\Core\Tests\BusinessLogic\Common\MockComponents
 */
class MockCountryConfigurationRepository implements CountryConfigurationRepositoryInterface
{
    /**
     * @var ?CountryConfiguration[]
     */
    private $countryConfigurations = [];

    /**
     * @inheritDoc
     */
    public function getCountryConfiguration(): ?array
    {
        return $this->countryConfigurations;
    }

    /**
     * @inheritDoc
     */
    public function setCountryConfiguration(array $countryConfigurations): void
    {
        $this->countryConfigurations = $countryConfigurations;
    }

    /**
     * @inheritDoc
     */
    public function deleteCountryConfigurations(): void
    {
        $this->countryConfigurations = null;
    }
}
