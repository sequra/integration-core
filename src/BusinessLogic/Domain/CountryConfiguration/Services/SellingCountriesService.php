<?php

namespace SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Services;

use Exception;
use SeQura\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
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

    /**
     * @var ConnectionService $connectionService
     */
    protected $connectionService;

    /**
     * @param SellingCountriesServiceInterface $integrationSellingCountriesService
     * @param ConnectionService $connectionService
     */
    public function __construct(
        SellingCountriesServiceInterface $integrationSellingCountriesService,
        ConnectionService $connectionService
    ) {
        $this->integrationSellingCountriesService = $integrationSellingCountriesService;
        $this->connectionService = $connectionService;
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
            $credentials = $this->connectionService->getCredentials();

            $credentialsByCountry = [];
            foreach ($credentials as $credential) {
                $credentialsByCountry[$credential->getCountry()] = $credential->getMerchantId();
            }

            $sellingCountries = [];
            foreach ($storeConfiguredCountryCodes as $code) {
                if (isset($credentialsByCountry[$code])) {
                    $sellingCountries[] = new SellingCountry(
                        $code,
                        SellingCountries::SELLING_COUNTRIES[$code] ?? $code,
                        $credentialsByCountry[$code]
                    );
                }
            }

            return $sellingCountries;
        } catch (Exception $e) {
            throw new FailedToRetrieveSellingCountriesException(new TranslatableLabel(
                'Failed to retrieve selling countries.',
                'general.errors.countries.sellingCountries'
            ));
        }
    }
}
