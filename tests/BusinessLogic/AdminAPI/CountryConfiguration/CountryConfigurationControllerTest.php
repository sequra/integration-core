<?php

namespace SeQura\Core\Tests\BusinessLogic\AdminAPI\CountryConfiguration;

use Exception;
use SeQura\Core\BusinessLogic\AdminAPI\AdminAPI;
use SeQura\Core\BusinessLogic\AdminAPI\CountryConfiguration\Requests\CountryConfigurationRequest;
use SeQura\Core\BusinessLogic\AdminAPI\CountryConfiguration\Responses\CountryConfigurationResponse;
use SeQura\Core\BusinessLogic\AdminAPI\CountryConfiguration\Responses\SellingCountriesResponse;
use SeQura\Core\BusinessLogic\AdminAPI\CountryConfiguration\Responses\SuccessfulCountryConfigurationResponse;
use SeQura\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Exceptions\EmptyCountryConfigurationParameterException;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Exceptions\FailedToRetrieveSellingCountriesException;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Exceptions\InvalidCountryCodeForConfigurationException;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Models\CountryConfiguration;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Models\SellingCountry;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\RepositoryContracts\CountryConfigurationRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Services\SellingCountriesService;
use SeQura\Core\BusinessLogic\Domain\Integration\SellingCountries\SellingCountriesServiceInterface;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockCoreSellingCountriesService;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockSellingCountriesService;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class CountryConfigurationControllerTest
 *
 * @package SeQura\Core\Tests\BusinessLogic\AdminAPI\CountryConfiguration
 */
class CountryConfigurationControllerTest extends BaseTestCase
{
    /**
     * @var CountryConfigurationRepositoryInterface
     */
    private $countryConfigurationRepository;

    public function setUp(): void
    {
        parent::setUp();

        TestServiceRegister::registerService(SellingCountriesServiceInterface::class, static function () {
            return new MockSellingCountriesService();
        });

        $this->countryConfigurationRepository = TestServiceRegister::getService(CountryConfigurationRepositoryInterface::class);
    }

    /**
     * @throws FailedToRetrieveSellingCountriesException
     */
    public function testIsGetSellingCountriesResponseSuccessful(): void
    {
        // Act
        $response = AdminAPI::get()->countryConfiguration('1')->getSellingCountries();

        // Assert
        self::assertTrue($response->isSuccessful());
    }

    /**
     * @throws FailedToRetrieveSellingCountriesException
     */
    public function testGetSellingCountriesResponse(): void
    {
        // Arrange
        $sellingCountriesService = new MockCoreSellingCountriesService(
            TestServiceRegister::getService(SellingCountriesServiceInterface::class),
            TestServiceRegister::getService(ConnectionService::class)
        );

        TestServiceRegister::registerService(
            SellingCountriesService::class,
            function () use ($sellingCountriesService) {
                return $sellingCountriesService;
            }
        );

        $sellingCountries = [
            new SellingCountry('CO', 'Colombia', 'logeecom1'),
            new SellingCountry('IT', 'Italy', 'logeecom2'),
            new SellingCountry('FR', 'France', 'logeecom3'),
            new SellingCountry('PE', 'Peru', 'logeecom4')
        ];

        $sellingCountriesService->setMockSellingCountries($sellingCountries);

        // Act
        $response = AdminAPI::get()->countryConfiguration('1')->getSellingCountries();
        $expectedResponse = new SellingCountriesResponse($sellingCountries);

        // Assert
        self::assertEquals($expectedResponse, $response);
    }

    /**
     * @throws FailedToRetrieveSellingCountriesException
     */
    public function testGetSellingCountriesResponseToArray(): void
    {
        // Arrange
        $sellingCountriesService = new MockCoreSellingCountriesService(
            TestServiceRegister::getService(SellingCountriesServiceInterface::class),
            TestServiceRegister::getService(ConnectionService::class)
        );

        TestServiceRegister::registerService(
            SellingCountriesService::class,
            function () use ($sellingCountriesService) {
                return $sellingCountriesService;
            }
        );

        $sellingCountries = [
            new SellingCountry('CO', 'Colombia', 'logeecom1'),
            new SellingCountry('IT', 'Italy', 'logeecom2'),
            new SellingCountry('FR', 'France', 'logeecom3'),
            new SellingCountry('PE', 'Peru', 'logeecom4')
        ];

        $sellingCountriesService->setMockSellingCountries($sellingCountries);


        // Act
        $response = AdminAPI::get()->countryConfiguration('1')->getSellingCountries();

        // Assert
        self::assertEquals($this->expectedSellingCountriesToArrayResponse(), $response->toArray());
    }


    public function testIsGetCountryConfigurationResponseSuccessful(): void
    {
        // Arrange
        $this->countryConfigurationRepository->setCountryConfiguration(
            [
                new CountryConfiguration('CO', 'logeecom'),
                new CountryConfiguration('ES', 'logeecom'),
                new CountryConfiguration('FR', 'logeecom')
            ]
        );

        // Act
        $response = AdminAPI::get()->countryConfiguration('1')->getCountryConfigurations();

        // Assert
        self::assertTrue($response->isSuccessful());
    }

    /**
     * @throws Exception
     */
    public function testGetCountryConfigurationResponse(): void
    {
        // Arrange
        $countryConfigurations = [
            new CountryConfiguration('CO', 'logeecom'),
            new CountryConfiguration('ES', 'logeecom'),
            new CountryConfiguration('FR', 'logeecom')
        ];
        $sellingCountriesService = new MockCoreSellingCountriesService(
            TestServiceRegister::getService(SellingCountriesServiceInterface::class),
            TestServiceRegister::getService(ConnectionService::class)
        );
        $sellingCountriesService->setMockSellingCountries([
            new SellingCountry('PT', 'Portugal', 'logeecom'),
            new SellingCountry('FR', 'France', 'logeecom'),
            new SellingCountry('IT', 'Italy', 'logeecom'),
            new SellingCountry('ES', 'Spain', 'logeecom'),
            new SellingCountry('CO', 'Columbia', 'logeecom'),
        ]);
        TestServiceRegister::registerService(
            SellingCountriesService::class,
            function () use ($sellingCountriesService) {
                return $sellingCountriesService;
            }
        );

        StoreContext::doWithStore(
            '1',
            [$this->countryConfigurationRepository, 'setCountryConfiguration'],
            [$countryConfigurations]
        );
        $expectedResponse = new CountryConfigurationResponse($countryConfigurations);

        // Act
        $response = AdminAPI::get()->countryConfiguration('1')->getCountryConfigurations();

        // Assert
        self::assertEquals($expectedResponse, $response);
    }

    /**
     * @throws Exception
     */
    public function testGetCountryConfigurationResponseToArray(): void
    {
        // Arrange
        $countryConfigurations = [
            new CountryConfiguration('CO', 'logeecom'),
            new CountryConfiguration('ES', 'logeecom'),
            new CountryConfiguration('FR', 'logeecom')
        ];
        $sellingCountriesService = new MockCoreSellingCountriesService(
            TestServiceRegister::getService(SellingCountriesServiceInterface::class),
            TestServiceRegister::getService(ConnectionService::class)
        );
        $sellingCountriesService->setMockSellingCountries([
            new SellingCountry('PT', 'Portugal', 'logeecom1'),
            new SellingCountry('FR', 'France', 'logeecom2'),
            new SellingCountry('IT', 'Italy', 'logeecom3'),
            new SellingCountry('ES', 'Spain', 'logeecom4'),
            new SellingCountry('CO', 'Columbia', 'logeecom5'),
        ]);
        TestServiceRegister::registerService(
            SellingCountriesService::class,
            function () use ($sellingCountriesService) {
                return $sellingCountriesService;
            }
        );

        StoreContext::doWithStore(
            '1',
            [$this->countryConfigurationRepository, 'setCountryConfiguration'],
            [$countryConfigurations]
        );

        // Act
        $response = AdminAPI::get()->countryConfiguration('1')->getCountryConfigurations();

        // Assert
        self::assertEquals($this->expectedToArrayResponse(), $response->toArray());
    }

    /**
     * @throws Exception
     */
    public function testGetNonExistingCountryConfigurationResponseToArray(): void
    {
        // Act
        $response = AdminAPI::get()->countryConfiguration('1')->getCountryConfigurations();

        // Assert
        self::assertEquals([], $response->toArray());
    }

    /**
     * @throws EmptyCountryConfigurationParameterException
     * @throws InvalidCountryCodeForConfigurationException
     */
    public function testIsSaveResponseSuccessful(): void
    {
        // Arrange
        $countryConfigurationRequest = new CountryConfigurationRequest([
            [
                'countryCode' => 'CO',
                'merchantId' => 'logeecom',
            ],
            [
                'countryCode' => 'ES',
                'merchantId' => 'logeecom',
            ],
            [
                'countryCode' => 'FR',
                'merchantId' => 'logeecom',
            ]
        ]);

        // Act
        $response = AdminAPI::get()->countryConfiguration('1')->saveCountryConfigurations($countryConfigurationRequest);

        // Assert
        self::assertTrue($response->isSuccessful());
    }

    /**
     * @throws EmptyCountryConfigurationParameterException
     * @throws InvalidCountryCodeForConfigurationException
     */
    public function testSaveResponse(): void
    {
        // Arrange
        $countryConfigurationRequest = new CountryConfigurationRequest([
            [
                'countryCode' => 'CO',
                'merchantId' => 'logeecom',
            ],
            [
                'countryCode' => 'ES',
                'merchantId' => 'logeecom',
            ],
            [
                'countryCode' => 'FR',
                'merchantId' => 'logeecom',
            ]
        ]);

        // Act
        $response = AdminAPI::get()->countryConfiguration('1')->saveCountryConfigurations($countryConfigurationRequest);
        $expectedResponse = new SuccessfulCountryConfigurationResponse();

        // Assert
        self::assertEquals($expectedResponse, $response);
    }

    /**
     * @throws EmptyCountryConfigurationParameterException
     * @throws InvalidCountryCodeForConfigurationException
     */
    public function testSaveResponseToArray(): void
    {
        // Arrange
        $countryConfigurationRequest = new CountryConfigurationRequest([
            [
                'countryCode' => 'CO',
                'merchantId' => 'logeecom',
            ],
            [
                'countryCode' => 'ES',
                'merchantId' => 'logeecom',
            ],
            [
                'countryCode' => 'FR',
                'merchantId' => 'logeecom',
            ]
        ]);

        // Act
        $response = AdminAPI::get()->countryConfiguration('1')->saveCountryConfigurations($countryConfigurationRequest);

        // Assert
        self::assertEquals([], $response->toArray());
    }

    /**
     * @throws Exception
     */
    public function testIsUpdateResponseSuccessful(): void
    {
        // Arrange
        $countryConfigurations = [
            new CountryConfiguration('CO', 'logeecom'),
            new CountryConfiguration('ES', 'logeecom'),
            new CountryConfiguration('FR', 'logeecom')
        ];

        StoreContext::doWithStore(
            '1',
            [$this->countryConfigurationRepository, 'setCountryConfiguration'],
            [$countryConfigurations]
        );

        $countryConfigurationRequest = new CountryConfigurationRequest([
            [
                'countryCode' => 'IT',
                'merchantId' => 'logeecom2',
            ],
            [
                'countryCode' => 'CO',
                'merchantId' => 'logeecom2',
            ],
            [
                'countryCode' => 'ES',
                'merchantId' => 'logeecom2',
            ]
        ]);

        // Act
        $response = AdminAPI::get()->countryConfiguration('1')->saveCountryConfigurations($countryConfigurationRequest);

        // Assert
        self::assertTrue($response->isSuccessful());
    }

    /**
     * @throws Exception
     */
    public function testUpdateResponse(): void
    {
        // Arrange
        $countryConfigurations = [
            new CountryConfiguration('CO', 'logeecom'),
            new CountryConfiguration('ES', 'logeecom'),
            new CountryConfiguration('FR', 'logeecom')
        ];

        StoreContext::doWithStore(
            '1',
            [$this->countryConfigurationRepository, 'setCountryConfiguration'],
            [$countryConfigurations]
        );

        $countryConfigurationRequest = new CountryConfigurationRequest([
            [
                'countryCode' => 'IT',
                'merchantId' => 'logeecom2',
            ],
            [
                'countryCode' => 'CO',
                'merchantId' => 'logeecom2',
            ],
            [
                'countryCode' => 'PT',
                'merchantId' => 'logeecom2',
            ]
        ]);

        // Act
        $response = AdminAPI::get()->countryConfiguration('1')->saveCountryConfigurations($countryConfigurationRequest);
        $expectedResponse = new SuccessfulCountryConfigurationResponse();

        // Assert
        self::assertEquals($expectedResponse, $response);
    }

    /**
     * @throws Exception
     */
    public function testUpdateResponseToArray(): void
    {
        // Arrange
        $countryConfigurations = [
            new CountryConfiguration('CO', 'logeecom'),
            new CountryConfiguration('ES', 'logeecom'),
            new CountryConfiguration('FR', 'logeecom')
        ];

        StoreContext::doWithStore(
            '1',
            [$this->countryConfigurationRepository, 'setCountryConfiguration'],
            [$countryConfigurations]
        );

        $countryConfigurationRequest = new CountryConfigurationRequest([
            [
                'countryCode' => 'IT',
                'merchantId' => 'logeecom2',
            ],
            [
                'countryCode' => 'CO',
                'merchantId' => 'logeecom2',
            ],
            [
                'countryCode' => 'PT',
                'merchantId' => 'logeecom2',
            ]
        ]);

        // Act
        $response = AdminAPI::get()->countryConfiguration('1')->saveCountryConfigurations($countryConfigurationRequest);

        // Assert
        self::assertEquals([], $response->toArray());
    }

    /**
     * @return array
     */
    private function expectedToArrayResponse(): array
    {
        return [
            [
                'countryCode' => 'CO',
                'merchantId' => 'logeecom',
            ],
            [
                'countryCode' => 'ES',
                'merchantId' => 'logeecom',
            ],
            [
                'countryCode' => 'FR',
                'merchantId' => 'logeecom',
            ]
        ];
    }

    private function expectedSellingCountriesToArrayResponse(): array
    {
        return [
            [
                'code' => 'CO',
                'name' => 'Colombia',
                'merchantId' => 'logeecom1'
            ],
            [
                'code' => 'IT',
                'name' => 'Italy',
                'merchantId' => 'logeecom2'
            ],
            [
                'code' => 'FR',
                'name' => 'France',
                'merchantId' => 'logeecom3'
            ],
            [
                'code' => 'PE',
                'name' => 'Peru',
                'merchantId' => 'logeecom4'
            ]
        ];
    }
}
