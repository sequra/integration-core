<?php

namespace SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Services;

use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Enum\SellingCountries;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Models\SellingCountry;
use SeQura\Core\BusinessLogic\Domain\Integration\SellingCountries\SellingCountriesServiceInterface;

/**
 * Class SellingCountriesService
 *
 * @package SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Services
 */
class SellingCountriesService
{
    /**
     * @var SellingCountriesServiceInterface
     */
    private $integrationSellingCountriesService;

    public function __construct(SellingCountriesServiceInterface $integrationSellingCountriesService)
    {
        $this->integrationSellingCountriesService = $integrationSellingCountriesService;
    }

    /**
     * Returns all available selling countries.
     *
     * @return SellingCountry[]
     */
    public function getSellingCountries(): array
    {
        $storeConfiguredCountryCodes = $this->integrationSellingCountriesService->getSellingCountries();

        $sellingCountries = [];
        foreach ($storeConfiguredCountryCodes as $code) {
            if(array_key_exists($code, SellingCountries::SELLING_COUNTRIES)) {
                $sellingCountries[] = new SellingCountry($code, SellingCountries::SELLING_COUNTRIES[$code]);
            }
        }

        return $sellingCountries;
    }
}
