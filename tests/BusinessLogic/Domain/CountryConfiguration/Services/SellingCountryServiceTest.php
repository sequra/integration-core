<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\CountryConfiguration\Services;

use Exception;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\Credentials;
use SeQura\Core\BusinessLogic\Domain\Connection\RepositoryContracts\ConnectionDataRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Connection\Services\CredentialsService;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Models\SellingCountry;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Services\SellingCountriesService;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Services\StoreIntegrationService;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockConnectionService;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockSellingCountriesService;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class SellingCountryServiceTest.
 *
 * @package SeQura\Core\Tests\BusinessLogic\Domain\CountryConfiguration\Services
 */
class SellingCountryServiceTest extends BaseTestCase
{
    /**
     * @var SellingCountriesService $sellingCountriesService
     */
    private $sellingCountriesService;

    /**
     * @var MockSellingCountriesService $integrationSellingCountriesService
     */
    private $integrationSellingCountriesService;

    /**
     * @var MockConnectionService $connectionService
     */
    private $connectionService;

    public function setUp(): void
    {
        parent::setUp();

        $this->integrationSellingCountriesService = new MockSellingCountriesService();
        $this->connectionService = new MockConnectionService(
            TestServiceRegister::getService(ConnectionDataRepositoryInterface::class),
            TestServiceRegister::getService(CredentialsService::class),
            TestServiceRegister::getService(StoreIntegrationService::class)
        );

        $this->sellingCountriesService = new SellingCountriesService(
            $this->integrationSellingCountriesService,
            $this->connectionService
        );
    }

    /**
     * @throws Exception
     */
    public function testGetSellingCountriesNoCountries(): void
    {
        // Arrange
        $this->connectionService->setMockCredentials([]);

        // Act
        $response = StoreContext::doWithStore('1', [$this->sellingCountriesService, 'getSellingCountries']);

        // Assert
        self::assertEmpty($response);
    }

    /**
     * @throws Exception
     */
    public function testGetSellingCountries(): void
    {
        // Arrange
        $credentials = [
            new Credentials('logeecom1', 'PT', 'EUR', 'assetsKey1', [], 'sequra'),
            new Credentials('logeecom2', 'FR', 'EUR', 'assetsKey2', [], 'sequra'),
            new Credentials('logeecom3', 'IT', 'EUR', 'assetsKey3', [], 'svea'),
            new Credentials('logeecom4', 'ES', 'EUR', 'assetsKey4', [], 'svea'),
        ];

        $this->integrationSellingCountriesService->setSellingCountries(['PT', 'FR', 'IT', 'ES']);
        $this->connectionService->setMockCredentials($credentials);

        StoreContext::doWithStore('1', [$this->sellingCountriesService, 'getSellingCountries']);

        // Act
        $response = StoreContext::doWithStore('1', [$this->sellingCountriesService, 'getSellingCountries']);

        // Assert
        $expectedResponse = [
            new SellingCountry('PT', 'Portugal', 'logeecom1'),
            new SellingCountry('FR', 'France', 'logeecom2'),
            new SellingCountry('IT', 'Italy', 'logeecom3'),
            new SellingCountry('ES', 'Spain', 'logeecom4'),
        ];

        self::assertCount(4, $response);
        self::assertEquals($expectedResponse, $response);
    }

    /**
     * @throws Exception
     */
    public function testGetSellingCountriesNotAllAvailableInIntegration(): void
    {
        // Arrange
        $credentials = [
            new Credentials('logeecom1', 'PT', 'EUR', 'assetsKey1', [], 'sequra'),
            new Credentials('logeecom2', 'FR', 'EUR', 'assetsKey2', [], 'svea'),
            new Credentials('logeecom3', 'IT', 'EUR', 'assetsKey3', [], 'sequra'),
            new Credentials('logeecom4', 'ES', 'EUR', 'assetsKey4', [], 'svea'),
        ];

        $this->integrationSellingCountriesService->setSellingCountries(['PT', 'FR']);
        $this->connectionService->setMockCredentials($credentials);

        StoreContext::doWithStore('1', [$this->sellingCountriesService, 'getSellingCountries']);

        // Act
        $response = StoreContext::doWithStore('1', [$this->sellingCountriesService, 'getSellingCountries']);

        // Assert
        $expectedResponse = [
            new SellingCountry('PT', 'Portugal', 'logeecom1'),
            new SellingCountry('FR', 'France', 'logeecom2')
        ];

        self::assertCount(2, $response);
        self::assertEquals($expectedResponse, $response);
    }

    /**
     * @throws Exception
     */
    public function testGetSellingCountriesNotAllAvailableForMerchant(): void
    {
        // Arrange
        $credentials = [
            new Credentials('logeecom3', 'IT', 'EUR', 'assetsKey3', [], 'sequra'),
            new Credentials('logeecom4', 'ES', 'EUR', 'assetsKey4', [], 'svea')
        ];

        $this->integrationSellingCountriesService->setSellingCountries(['PT', 'FR', 'IT', 'ES']);
        $this->connectionService->setMockCredentials($credentials);

        StoreContext::doWithStore('1', [$this->sellingCountriesService, 'getSellingCountries']);

        // Act
        $response = StoreContext::doWithStore('1', [$this->sellingCountriesService, 'getSellingCountries']);

        // Assert
        $expectedResponse = [
            new SellingCountry('IT', 'Italy', 'logeecom3'),
            new SellingCountry('ES', 'Spain', 'logeecom4')
        ];

        self::assertCount(2, $response);
        self::assertEquals($expectedResponse, $response);
    }

    /**
     * @throws Exception
     */
    public function testGetSellingCountriesNoAvailableCountries(): void
    {
        // Arrange
        $credentials = [
            new Credentials('logeecom3', 'IT', 'EUR', 'assetsKey3', [], 'sequra'),
            new Credentials('logeecom4', 'ES', 'EUR', 'assetsKey4', [], 'svea')
        ];

        $this->integrationSellingCountriesService->setSellingCountries(['PT', 'FR']);
        $this->connectionService->setMockCredentials($credentials);

        StoreContext::doWithStore('1', [$this->sellingCountriesService, 'getSellingCountries']);

        // Act
        $response = StoreContext::doWithStore('1', [$this->sellingCountriesService, 'getSellingCountries']);

        // Assert
        self::assertEmpty($response);
    }
}
