<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\CountryConfiguration\Services;

use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Models\CountryConfiguration;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Models\SellingCountry;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\RepositoryContracts\CountryConfigurationRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Services\CountryConfigurationService;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Services\SellingCountriesService;
use SeQura\Core\Infrastructure\Logger\Interfaces\ShopLoggerAdapter;
use SeQura\Core\Infrastructure\Logger\LogContextData;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\Logger\TestShopLogger;
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
     * @return void
     */
    public function testSaveCountryConfigurationForCountriesCodesLeavesOutACountryWithNoMerchant(): void
    {
        // Arrange
        $this->sellsIn(['ES']);

        // Act
        $this->service->saveCountryConfigurationForCountriesCodes(['ES', 'FR']);

        // Assert
        self::assertSame(['ES'], array_map(static function (CountryConfiguration $configured) {
            return $configured->getCountryCode();
        }, $this->repository->getCountryConfiguration()));
    }

    /**
     * @return void
     */
    public function testSaveCountryConfigurationForCountriesCodesLogsTheCountriesLeftOut(): void
    {
        // Arrange
        $logger = new TestShopLogger();
        TestServiceRegister::registerService(ShopLoggerAdapter::CLASS_NAME, static function () use ($logger) {
            return $logger;
        });
        $this->sellsIn(['ES']);

        // Act
        $this->service->saveCountryConfigurationForCountriesCodes(['ES', 'FR']);

        // Assert
        self::assertTrue(
            $logger->isMessageContainedInLog('Countries were left out of the country configuration')
        );
        self::assertSame(
            ['skippedCountries' => 'FR', 'savedCountries' => 'ES'],
            $this->contextOf($logger->data->getContext())
        );
    }

    /**
     * @param LogContextData[] $context
     *
     * @return string[]
     */
    private function contextOf(array $context): array
    {
        $values = [];
        foreach ($context as $item) {
            $values[$item->getName()] = $item->getValue();
        }

        return $values;
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
