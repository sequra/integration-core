<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\CountryConfiguration\Requests;

use SeQura\Core\BusinessLogic\AdminAPI\Request\Request;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Exceptions\EmptyCountryConfigurationParameterException;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Exceptions\InvalidCountryCodeForConfigurationException;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Models\CountryConfiguration;

/**
 * Class CountryConfigurationRequest
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\CountryConfiguration\Requests
 */
class CountryConfigurationRequest extends Request
{
    /**
     * @var array
     */
    protected $countryConfigurations;

    /**
     * @param array $countryConfigurations
     */
    public function __construct(array $countryConfigurations)
    {
        $this->countryConfigurations = $countryConfigurations;
    }

    /**
     * Transforms the request array to an array of CountryConfigurations.
     *
     * @return CountryConfiguration[]
     *
     * @throws InvalidCountryCodeForConfigurationException
     * @throws EmptyCountryConfigurationParameterException
     */
    public function transformToDomainModel(): array
    {
        $configs = [];
        foreach ($this->countryConfigurations as $configuration) {
            $configs[] = new CountryConfiguration(
                $configuration['countryCode'] ?? '',
                $configuration['merchantId'] ?? ''
            );
        }

        return $configs;
    }
}
