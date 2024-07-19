<?php

namespace SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Services;

use Exception;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Enum\SellingCountries;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Exceptions\FailedToRetrieveSellingCountriesException;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Models\SellingCountry;
use SeQura\Core\BusinessLogic\Domain\Integration\SellingCountries\SellingCountriesServiceInterface;
use SeQura\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;

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
    protected $integrationSellingCountriesService;

    public function __construct(SellingCountriesServiceInterface $integrationSellingCountriesService)
    {
        $this->integrationSellingCountriesService = $integrationSellingCountriesService;
    }

    /**
     * Returns all available selling countries.
     *
     * @return SellingCountry[]
     *
     * @throws FailedToRetrieveSellingCountriesException
     */
    public function getSellingCountries(): array
    {
        try {
            $storeConfiguredCountryCodes = $this->integrationSellingCountriesService->getSellingCountries();

            $sellingCountries = [];
            foreach ($storeConfiguredCountryCodes as $code) {
                if (array_key_exists($code, SellingCountries::SELLING_COUNTRIES)) {
                    $sellingCountries[] = new SellingCountry($code, SellingCountries::SELLING_COUNTRIES[$code]);
                }
            }

            return $sellingCountries;
        } catch (Exception $e) {
            throw new FailedToRetrieveSellingCountriesException(new TranslatableLabel('Failed to retrieve selling countries.', 'general.errors.countries.sellingCountries'));
        }
    }
}
