<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\CountryConfiguration\Responses;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Models\CountryConfiguration;

/**
 * Class CountryConfigurationResponse
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\CountryConfiguration\Responses
 */
class CountryConfigurationResponse extends Response
{
    /**
     * @var CountryConfiguration[]
     */
    protected $countryConfigurations;

    /**
     * @param CountryConfiguration[]|null $countryConfigurations
     */
    public function __construct(?array $countryConfigurations)
    {
        $this->countryConfigurations = $countryConfigurations;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        if (!$this->countryConfigurations) {
            return [];
        }

        $configs = [];
        foreach ($this->countryConfigurations as $configuration) {
            $configs[] = [
                'countryCode' => $configuration->getCountryCode(),
                'merchantId' => $configuration->getMerchantId()
            ];
        }

        return $configs;
    }
}
