<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\CountryConfiguration\Services;

use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Models\CountryConfiguration;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Models\SellingCountry;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\RepositoryContracts\CountryConfigurationRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Services\CountryConfigurationService;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Services\SellingCountriesService;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class CountryConfigurationServiceTest.
 *
 * @package SeQura\Core\Tests\BusinessLogic\Domain\CountryConfiguration\Services
 */
class CountryConfigurationServiceTest extends BaseTestCase
{
    /**
     * @var CountryConfigurationRepositoryInterface $repository
     */
    private $repository;

    /**
     * @var SellingCountriesService $sellingCountriesService
     */
    private $sellingCountriesService;

    /**
     * @var CountryConfigurationService $service
     */
    private $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->repository = TestServiceRegister::getService(CountryConfigurationRepositoryInterface::class);
        $this->sellingCountriesService = $this->createMock(SellingCountriesService::class);

        $this->service = new CountryConfigurationService($this->repository, $this->sellingCountriesService);
    }

    /**
     * @return void
     */
    public function testGetCountryConfigurationIsAListAfterDroppingACountryNoLongerSoldIn(): void
    {
        // The keys reach json_encode untouched, so a gap in them is the difference
        // between a JSON list and a JSON object for every consumer of this.
        $this->repository->setCountryConfiguration([
            new CountryConfiguration('ES', 'merchant_es'),
            new CountryConfiguration('IT', 'merchant_it'),
            new CountryConfiguration('FR', 'merchant_fr'),
            new CountryConfiguration('PT', 'merchant_pt'),
        ]);
        $this->sellsIn(['ES', 'FR', 'PT']);

        $configuration = $this->service->getCountryConfiguration();

        self::assertSame([0, 1, 2], array_keys($configuration));
        self::assertSame(['ES', 'FR', 'PT'], array_map(function (CountryConfiguration $configured) {
            return $configured->getCountryCode();
        }, $configuration));
    }

    /**
     * @return void
     */
    public function testGetCountryCodesIsAListAfterDroppingACountryNoLongerSoldIn(): void
    {
        $this->repository->setCountryConfiguration([
            new CountryConfiguration('ES', 'merchant_es'),
            new CountryConfiguration('IT', 'merchant_it'),
            new CountryConfiguration('FR', 'merchant_fr'),
        ]);
        $this->sellsIn(['ES', 'FR']);

        self::assertSame(['ES', 'FR'], $this->service->getCountryCodes());
    }

    /**
     * @param string[] $countryCodes
     *
     * @return void
     */
    private function sellsIn(array $countryCodes): void
    {
        $sellingCountries = [];
        foreach ($countryCodes as $countryCode) {
            $sellingCountries[] = new SellingCountry($countryCode, $countryCode, 'merchant_' . strtolower($countryCode));
        }

        $this->sellingCountriesService->method('getSellingCountries')->willReturn($sellingCountries);
    }
}
