<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\PromotionalWidgets\Services;

use Exception;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidEnvironmentException;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\AuthorizationCredentials;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\Credentials;
use SeQura\Core\BusinessLogic\Domain\Connection\RepositoryContracts\ConnectionDataRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Connection\RepositoryContracts\CredentialsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use SeQura\Core\BusinessLogic\Domain\Connection\Services\CredentialsService;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Models\CountryConfiguration;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\RepositoryContracts\CountryConfigurationRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Services\CountryConfigurationService;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Services\SellingCountriesService;
use SeQura\Core\BusinessLogic\Domain\Integration\PromotionalWidgets\WidgetConfiguratorInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\SellingCountries\SellingCountriesServiceInterface;
use SeQura\Core\BusinessLogic\Domain\Merchant\ProxyContracts\MerchantProxyInterface;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Exceptions\PaymentMethodNotFoundException;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Models\SeQuraCost;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Models\SeQuraPaymentMethod;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\RepositoryContracts\PaymentMethodRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Services\PaymentMethodsService;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\CustomWidgetsSettings;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\Widget;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\WidgetSelectorSettings;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\WidgetSettings as WidgetSettingsModel;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\RepositoryContracts\WidgetSettingsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Services\WidgetSettingsService;
use SeQura\Core\Infrastructure\Http\Exceptions\HttpRequestException;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockConnectionService;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockCountryConfigurationService;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockCredentialsRepository;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockPaymentMethodService;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockSellingCountriesService;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockWidgetConfigurator;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockWidgetSettingsRepository;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class WidgetSettingsServiceTest.
 *
 * @package SeQura\Core\Tests\BusinessLogic\Domain\PromotionalWidgets\Services
 */
class WidgetSettingsServiceTest extends BaseTestCase
{
    /**
     * @var MockWidgetSettingsRepository
     */
    private $widgetSettingsRepository;

    /**
     * @var MockPaymentMethodService
     */
    private $paymentMethodsService;

    /**
     * @var MockCountryConfigurationService
     */
    private $countryConfigService;

    /**
     * @var MockCredentialsRepository
     */
    private $credentialsRepository;

    /**
     * @var MockConnectionService
     */
    private $connectionService;

    /**
     * @var WidgetSettingsService
     */
    private $widgetSettingsService;

    /**
     * @var MockWidgetConfigurator
     */
    private $widgetConfigurator;

    /**
     * @return void
     *
     * @throws RepositoryClassException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->widgetSettingsRepository = new MockWidgetSettingsRepository();
        TestServiceRegister::registerService(WidgetSettingsRepositoryInterface::class, function () {
            return $this->widgetSettingsRepository;
        });

        TestServiceRegister::registerService(
            SellingCountriesServiceInterface::class,
            static function () {
                return new MockSellingCountriesService();
            }
        );

        $this->paymentMethodsService = new MockPaymentMethodService(
            TestServiceRegister::getService(MerchantProxyInterface::class),
            TestServiceRegister::getService(PaymentMethodRepositoryInterface::class),
            TestServiceRegister::getService(CountryConfigurationService::class)
        );

        TestServiceRegister::registerService(PaymentMethodsService::class, function () {
            return $this->paymentMethodsService;
        });

        $this->credentialsRepository = new MockCredentialsRepository();
        TestServiceRegister::registerService(CredentialsRepositoryInterface::class, function () {
            return $this->credentialsRepository;
        });

        $this->countryConfigService = new MockCountryConfigurationService(
            TestServiceRegister::getService(CountryConfigurationRepositoryInterface::class),
            TestServiceRegister::getService(SellingCountriesService::class)
        );

        TestServiceRegister::registerService(CountryConfigurationService::class, function () {
            return $this->countryConfigService;
        });

        $this->connectionService = new MockConnectionService(
            TestServiceRegister::getService(ConnectionDataRepositoryInterface::class),
            TestServiceRegister::getService(CredentialsService::class)
        );

        TestServiceRegister::registerService(ConnectionService::class, function () {
            return $this->connectionService;
        });

        $this->widgetConfigurator = new MockWidgetConfigurator();

        TestServiceRegister::registerService(WidgetConfiguratorInterface::class, function () {
            return $this->widgetConfigurator;
        });

        $this->widgetSettingsService = TestServiceRegister::getService(WidgetSettingsService::class);
    }

    /**
     * @return void
     *
     * @throws InvalidEnvironmentException
     * @throws PaymentMethodNotFoundException
     * @throws HttpRequestException
     */
    public function testGetScriptUriForSandbox(): void
    {
        //arrange
        $this->countryConfigService->saveCountryConfiguration([new CountryConfiguration('ES', 'ES')]);
        $this->connectionService->saveConnectionData(
            new ConnectionData(
                'sandbox',
                'test',
                new AuthorizationCredentials('test_username', 'test_password')
            )
        );

        // act
        $widgetInitialize = $this->widgetSettingsService->getWidgetInitializeData('ES', 'ES');

        // assert
        self::assertEquals(
            'https://sandbox.sequracdn.com/assets/sequra-checkout.min.js',
            $widgetInitialize->getScriptUri()
        );
    }

    /**
     * @return void
     *
     * @throws InvalidEnvironmentException
     * @throws PaymentMethodNotFoundException
     * @throws HttpRequestException
     */
    public function testGetScriptUriForLive(): void
    {
        //arrange
        $this->countryConfigService->saveCountryConfiguration([new CountryConfiguration('ES', 'ES')]);
        $this->connectionService->saveConnectionData(
            new ConnectionData(
                'live',
                'test',
                new AuthorizationCredentials('test_username', 'test_password')
            )
        );

        // act
        $widgetInitialize = $this->widgetSettingsService->getWidgetInitializeData('ES', 'ES');

        // assert
        self::assertEquals(
            'https://live.sequracdn.com/assets/sequra-checkout.min.js',
            $widgetInitialize->getScriptUri()
        );
    }

    /**
     * @return void
     *
     * @throws HttpRequestException
     * @throws PaymentMethodNotFoundException
     */
    public function testGetScriptUriNoConnectionData(): void
    {
        //arrange
        $this->countryConfigService->saveCountryConfiguration([new CountryConfiguration('ES', 'ES')]);

        // act
        $widgetInitialize = $this->widgetSettingsService->getWidgetInitializeData('ES', 'ES');

        // assert
        self::assertEquals(
            '',
            $widgetInitialize->getScriptUri()
        );
    }

    /**
     * @return void
     *
     * @throws HttpRequestException
     * @throws InvalidEnvironmentException
     * @throws PaymentMethodNotFoundException
     */
    public function testGetWidgetInitializeDataMerchantIdFromShippingCountry(): void
    {
        //arrange
        $this->credentialsRepository->setCredentials(
            [
                new Credentials('merchantES', 'ES', 'EUR', 'asset', []),
                new Credentials('merchantFR', 'FR', 'EUR', 'asset', []),
                new Credentials('merchantIT', 'IT', 'EUR', 'asset', []),
            ]
        );
        $this->connectionService->saveConnectionData(
            new ConnectionData(
                'live',
                'test',
                new AuthorizationCredentials('test_username', 'test_password')
            )
        );

        // act
        $widgetInitialize = $this->widgetSettingsService->getWidgetInitializeData('ES', 'FR');

        // assert
        self::assertEquals(
            'merchantES',
            $widgetInitialize->getMerchantId()
        );
    }

    /**
     * @return void
     *
     * @throws HttpRequestException
     * @throws InvalidEnvironmentException
     * @throws PaymentMethodNotFoundException
     */
    public function testGetWidgetInitializeDataMerchantIdFromCurrentCountry(): void
    {
        //arrange
        $this->countryConfigService->saveCountryConfiguration(
            [
                new CountryConfiguration('ES', 'merchantES'),
                new CountryConfiguration('FR', 'merchantFR'),
                new CountryConfiguration('IT', 'merchantIT'),
            ]
        );
        $this->connectionService->saveConnectionData(
            new ConnectionData(
                'live',
                'test',
                new AuthorizationCredentials('test_username', 'test_password')
            )
        );

        // act
        $widgetInitialize = $this->widgetSettingsService->getWidgetInitializeData('PT', 'FR');

        // assert
        self::assertEquals(
            'merchantFR',
            $widgetInitialize->getMerchantId()
        );
    }

    /**
     * @return void
     *
     * @throws HttpRequestException
     * @throws InvalidEnvironmentException
     * @throws PaymentMethodNotFoundException
     */
    public function testGetWidgetInitializeDataNoId(): void
    {
        //arrange
        $this->countryConfigService->saveCountryConfiguration(
            [
                new CountryConfiguration('ES', 'merchantES'),
                new CountryConfiguration('FR', 'merchantFR'),
                new CountryConfiguration('IT', 'merchantIT'),
            ]
        );
        $this->connectionService->saveConnectionData(
            new ConnectionData(
                'live',
                'test',
                new AuthorizationCredentials('test_username', 'test_password')
            )
        );

        // act
        $widgetInitialize = $this->widgetSettingsService->getWidgetInitializeData('PT', 'CO');

        // assert
        self::assertEquals(
            '',
            $widgetInitialize->getMerchantId()
        );
    }

    /**
     * @return void
     *
     * @throws HttpRequestException
     * @throws InvalidEnvironmentException
     * @throws PaymentMethodNotFoundException
     */
    public function testGetWidgetInitializeDataNoProducts(): void
    {
        //arrange
        $this->countryConfigService->saveCountryConfiguration(
            [
                new CountryConfiguration('ES', 'merchantES'),
                new CountryConfiguration('FR', 'merchantFR'),
                new CountryConfiguration('IT', 'merchantIT'),
            ]
        );
        $this->connectionService->saveConnectionData(
            new ConnectionData(
                'live',
                'test',
                new AuthorizationCredentials('test_username', 'test_password')
            )
        );

        // act
        $widgetInitialize = $this->widgetSettingsService->getWidgetInitializeData('PT', 'CO');

        // assert
        self::assertEmpty($widgetInitialize->getProducts());
    }

    /**
     * @return void
     *
     * @throws HttpRequestException
     * @throws InvalidEnvironmentException
     * @throws PaymentMethodNotFoundException
     */
    public function testGetWidgetInitializeDataDefaultValues(): void
    {
        //arrange
        $this->countryConfigService->saveCountryConfiguration(
            [
                new CountryConfiguration('ES', 'merchantES'),
                new CountryConfiguration('FR', 'merchantFR'),
                new CountryConfiguration('IT', 'merchantIT'),
            ]
        );
        $this->connectionService->saveConnectionData(
            new ConnectionData(
                'live',
                'test',
                new AuthorizationCredentials('test_username', 'test_password')
            )
        );
        $this->paymentMethodsService->setMockProducts(['i1', 'pp5', 'pp3', 'payment7', 'payment5']);

        $this->paymentMethodsService->setMockPaymentMethods(
            [
                new SeQuraPaymentMethod(
                    'i1',
                    'Payment1',
                    'Description1',
                    'pay_later',
                    new SeQuraCost(0, 0, 0, 0),
                    new \DateTime('0022-02-22T22:36:44Z'),
                    new \DateTime('0022-02-22T22:36:44Z'),
                    null,
                    ''
                ),
                new SeQuraPaymentMethod(
                    'pp5',
                    'Payment2',
                    'Description2',
                    'pay_later',
                    new SeQuraCost(0, 0, 0, 0),
                    new \DateTime('0022-02-22T22:36:44Z'),
                    new \DateTime('0022-02-22T22:36:44Z'),
                    null,
                    ''
                ),
                new SeQuraPaymentMethod(
                    'pp3',
                    'Payment3',
                    'Description3',
                    'pay_later',
                    new SeQuraCost(0, 0, 0, 0),
                    new \DateTime('0022-02-22T22:36:44Z'),
                    new \DateTime('0022-02-22T22:36:44Z'),
                    null,
                    ''
                ),
                new SeQuraPaymentMethod(
                    'payment7',
                    'Payment7',
                    'Description7',
                    'pay_later',
                    new SeQuraCost(0, 0, 0, 0),
                    new \DateTime('0022-02-22T22:36:44Z'),
                    new \DateTime('0022-02-22T22:36:44Z'),
                    null,
                    ''
                ),
                new SeQuraPaymentMethod(
                    'payment5',
                    'Payment5',
                    'Description5',
                    'pay_later',
                    new SeQuraCost(0, 0, 0, 0),
                    new \DateTime('0022-02-22T22:36:44Z'),
                    new \DateTime('0022-02-22T22:36:44Z'),
                    null,
                    ''
                )
            ]
        );

        // act
        $widgetInitialize = $this->widgetSettingsService->getWidgetInitializeData('FR', 'FR');

        // assert
        self::assertEquals(['i1', 'pp5', 'pp3', 'payment7', 'payment5'], $widgetInitialize->getProducts());
        self::assertEquals('es-ES', $widgetInitialize->getLocale());
        self::assertEquals('EUR', $widgetInitialize->getCurrency());
        self::assertEquals(',', $widgetInitialize->getDecimalSeparator());
        self::assertEquals('.', $widgetInitialize->getThousandSeparator());
    }

    /**
     * @return void
     *
     * @throws HttpRequestException
     * @throws InvalidEnvironmentException
     * @throws PaymentMethodNotFoundException
     */
    public function testGetWidgetInitializeData(): void
    {
        //arrange
        $this->countryConfigService->saveCountryConfiguration(
            [
                new CountryConfiguration('ES', 'merchantES'),
                new CountryConfiguration('FR', 'merchantFR'),
                new CountryConfiguration('IT', 'merchantIT'),
            ]
        );
        $this->connectionService->saveConnectionData(
            new ConnectionData(
                'live',
                'test',
                new AuthorizationCredentials('test_username', 'test_password')
            )
        );
        $this->paymentMethodsService->setMockProducts(['i1', 'pp5', 'pp3', 'payment7', 'payment5']);
        $this->widgetConfigurator->setMockLocale('en-US');
        $this->widgetConfigurator->setMockCurrency('USD');
        $this->widgetConfigurator->setMockDecimalSeparator('!');
        $this->widgetConfigurator->setMockThousandSeparator('?');
        $this->paymentMethodsService->setMockPaymentMethods(
            [
                new SeQuraPaymentMethod(
                    'i1',
                    'Payment1',
                    'Description1',
                    'pay_later',
                    new SeQuraCost(0, 0, 0, 0),
                    new \DateTime('0022-02-22T22:36:44Z'),
                    new \DateTime('0022-02-22T22:36:44Z'),
                    null,
                    ''
                ),
                new SeQuraPaymentMethod(
                    'pp5',
                    'Payment2',
                    'Description2',
                    'pay_later',
                    new SeQuraCost(0, 0, 0, 0),
                    new \DateTime('0022-02-22T22:36:44Z'),
                    new \DateTime('0022-02-22T22:36:44Z'),
                    null,
                    ''
                ),
                new SeQuraPaymentMethod(
                    'pp3',
                    'Payment3',
                    'Description3',
                    'pay_later',
                    new SeQuraCost(0, 0, 0, 0),
                    new \DateTime('0022-02-22T22:36:44Z'),
                    new \DateTime('0022-02-22T22:36:44Z'),
                    null,
                    ''
                ),
                new SeQuraPaymentMethod(
                    'payment7',
                    'Payment7',
                    'Description7',
                    'pay_later',
                    new SeQuraCost(0, 0, 0, 0),
                    new \DateTime('0022-02-22T22:36:44Z'),
                    new \DateTime('0022-02-22T22:36:44Z'),
                    null,
                    ''
                ),
                new SeQuraPaymentMethod(
                    'payment5',
                    'Payment5',
                    'Description5',
                    'pay_later',
                    new SeQuraCost(0, 0, 0, 0),
                    new \DateTime('0022-02-22T22:36:44Z'),
                    new \DateTime('0022-02-22T22:36:44Z'),
                    null,
                    ''
                )
            ]
        );

        // act
        $widgetInitialize = $this->widgetSettingsService->getWidgetInitializeData('FR', 'FR');

        // assert
        self::assertEquals(['i1', 'pp5', 'pp3', 'payment7', 'payment5'], $widgetInitialize->getProducts());
        self::assertEquals('en-US', $widgetInitialize->getLocale());
        self::assertEquals('USD', $widgetInitialize->getCurrency());
        self::assertEquals('!', $widgetInitialize->getDecimalSeparator());
        self::assertEquals('?', $widgetInitialize->getThousandSeparator());
    }

    /**
     * @return void
     *
     * @throws HttpRequestException
     * @throws InvalidEnvironmentException
     * @throws PaymentMethodNotFoundException
     */
    public function testGetGetAvailableWidgetForCartPageNoWidgetSettings(): void
    {
        //arrange
        $this->countryConfigService->saveCountryConfiguration(
            [
                new CountryConfiguration('ES', 'merchantES'),
                new CountryConfiguration('FR', 'merchantFR'),
                new CountryConfiguration('IT', 'merchantIT'),
            ]
        );
        $this->connectionService->saveConnectionData(
            new ConnectionData(
                'live',
                'test',
                new AuthorizationCredentials('test_username', 'test_password')
            )
        );

        // act
        $widget = $this->widgetSettingsService->getAvailableWidgetForCartPage('FR', 'FR');

        // assert
        self::assertNull($widget);
    }

    /**
     * @return void
     *
     * @throws HttpRequestException
     * @throws InvalidEnvironmentException
     * @throws PaymentMethodNotFoundException
     * @throws Exception
     */
    public function testGetGetAvailableWidgetForCartPageWidgetSettingsDisabled(): void
    {
        //arrange
        $this->countryConfigService->saveCountryConfiguration(
            [
                new CountryConfiguration('ES', 'merchantES'),
                new CountryConfiguration('FR', 'merchantFR'),
                new CountryConfiguration('IT', 'merchantIT'),
            ]
        );
        $this->connectionService->saveConnectionData(
            new ConnectionData(
                'live',
                'test',
                new AuthorizationCredentials('test_username', 'test_password')
            )
        );
        $this->widgetSettingsRepository->setWidgetSettings(new WidgetSettingsModel(false));

        // act
        $widget = $this->widgetSettingsService->getAvailableWidgetForCartPage('FR', 'FR');

        // assert
        self::assertNull($widget);
    }

    /**
     * @return void
     *
     * @throws HttpRequestException
     * @throws InvalidEnvironmentException
     * @throws PaymentMethodNotFoundException
     * @throws Exception
     */
    public function testGetGetAvailableWidgetForCartPageNoWidgetSettingsForCart(): void
    {
        //arrange
        $this->countryConfigService->saveCountryConfiguration(
            [
                new CountryConfiguration('ES', 'merchantES'),
                new CountryConfiguration('FR', 'merchantFR'),
                new CountryConfiguration('IT', 'merchantIT'),
            ]
        );
        $this->connectionService->saveConnectionData(
            new ConnectionData(
                'live',
                'test',
                new AuthorizationCredentials('test_username', 'test_password')
            )
        );
        $this->widgetSettingsRepository->setWidgetSettings(new WidgetSettingsModel(true));

        // act
        $widget = $this->widgetSettingsService->getAvailableWidgetForCartPage('FR', 'FR');

        // assert
        self::assertNull($widget);
    }

    /**
     * @return void
     *
     * @throws HttpRequestException
     * @throws InvalidEnvironmentException
     * @throws PaymentMethodNotFoundException
     * @throws Exception
     */
    public function testGetGetAvailableWidgetForCartPageNoCachedPaymentMethods(): void
    {
        //arrange
        $this->countryConfigService->saveCountryConfiguration(
            [
                new CountryConfiguration('ES', 'merchantES'),
                new CountryConfiguration('FR', 'merchantFR'),
                new CountryConfiguration('IT', 'merchantIT'),
            ]
        );
        $this->connectionService->saveConnectionData(
            new ConnectionData(
                'live',
                'test',
                new AuthorizationCredentials('test_username', 'test_password')
            )
        );
        $this->widgetSettingsRepository->setWidgetSettings(new WidgetSettingsModel(
            true,
            false,
            false,
            false,
            '',
            null,
            new WidgetSelectorSettings('test', 'test'),
            null
        ));

        // act
        $widget = $this->widgetSettingsService->getAvailableWidgetForCartPage('FR', 'FR');

        // assert
        self::assertNull($widget);
    }

    /**
     * @return void
     *
     * @throws HttpRequestException
     * @throws InvalidEnvironmentException
     * @throws PaymentMethodNotFoundException
     * @throws Exception
     */
    public function testGetGetAvailableWidgetForCartPagePaymentMethodNotFound(): void
    {
        //arrange
        $this->countryConfigService->saveCountryConfiguration(
            [
                new CountryConfiguration('ES', 'merchantES'),
                new CountryConfiguration('FR', 'merchantFR'),
                new CountryConfiguration('IT', 'merchantIT'),
            ]
        );
        $this->connectionService->saveConnectionData(
            new ConnectionData(
                'live',
                'test',
                new AuthorizationCredentials('test_username', 'test_password')
            )
        );
        $this->widgetSettingsRepository->setWidgetSettings(new WidgetSettingsModel(
            true,
            false,
            false,
            false,
            '',
            null,
            new WidgetSelectorSettings('test', 'test', 'i1'),
            null
        ));
        $this->paymentMethodsService->setMockPaymentMethods(
            [
                new SeQuraPaymentMethod(
                    'pp5',
                    'Payment2',
                    'Description2',
                    'pay_later',
                    new SeQuraCost(0, 0, 0, 0),
                    new \DateTime('0022-02-22T22:36:44Z'),
                    new \DateTime('0022-02-22T22:36:44Z'),
                    null,
                    ''
                ),
                new SeQuraPaymentMethod(
                    'pp3',
                    'Payment3',
                    'Description3',
                    'pay_later',
                    new SeQuraCost(0, 0, 0, 0),
                    new \DateTime('0022-02-22T22:36:44Z'),
                    new \DateTime('0022-02-22T22:36:44Z'),
                    null,
                    ''
                ),
                new SeQuraPaymentMethod(
                    'payment7',
                    'Payment7',
                    'Description7',
                    'pay_later',
                    new SeQuraCost(0, 0, 0, 0),
                    new \DateTime('0022-02-22T22:36:44Z'),
                    new \DateTime('0022-02-22T22:36:44Z'),
                    null,
                    ''
                ),
                new SeQuraPaymentMethod(
                    'payment5',
                    'Payment5',
                    'Description5',
                    'pay_later',
                    new SeQuraCost(0, 0, 0, 0),
                    new \DateTime('0022-02-22T22:36:44Z'),
                    new \DateTime('0022-02-22T22:36:44Z'),
                    null,
                    ''
                )
            ]
        );

        // act
        $widget = $this->widgetSettingsService->getAvailableWidgetForCartPage('FR', 'FR');

        // assert
        self::assertNull($widget);
    }

    /**
     * @return void
     *
     * @throws HttpRequestException
     * @throws InvalidEnvironmentException
     * @throws PaymentMethodNotFoundException
     * @throws Exception
     */
    public function testGetGetAvailableWidgetForCart(): void
    {
        //arrange
        $this->countryConfigService->saveCountryConfiguration(
            [
                new CountryConfiguration('ES', 'merchantES'),
                new CountryConfiguration('FR', 'merchantFR'),
                new CountryConfiguration('IT', 'merchantIT'),
            ]
        );
        $this->connectionService->saveConnectionData(
            new ConnectionData(
                'live',
                'test',
                new AuthorizationCredentials('test_username', 'test_password')
            )
        );
        $this->widgetSettingsRepository->setWidgetSettings(new WidgetSettingsModel(
            true,
            false,
            false,
            true,
            'configTest',
            null,
            new WidgetSelectorSettings('location1', 'location2', 'i1'),
            null
        ));
        $this->paymentMethodsService->setMockPaymentMethods(
            [
                new SeQuraPaymentMethod(
                    'i1',
                    'Payment2',
                    'Description2',
                    'pay_later',
                    new SeQuraCost(0, 0, 0, 0),
                    new \DateTime('0022-02-22T22:36:44Z'),
                    new \DateTime('0022-02-22T22:36:44Z'),
                    'campaign1',
                    ''
                ),
                new SeQuraPaymentMethod(
                    'pp3',
                    'Payment3',
                    'Description3',
                    'pay_later',
                    new SeQuraCost(0, 0, 0, 0),
                    new \DateTime('0022-02-22T22:36:44Z'),
                    new \DateTime('0022-02-22T22:36:44Z'),
                    null,
                    ''
                ),
                new SeQuraPaymentMethod(
                    'payment7',
                    'Payment7',
                    'Description7',
                    'pay_later',
                    new SeQuraCost(0, 0, 0, 0),
                    new \DateTime('0022-02-22T22:36:44Z'),
                    new \DateTime('0022-02-22T22:36:44Z'),
                    null,
                    ''
                ),
                new SeQuraPaymentMethod(
                    'payment5',
                    'Payment5',
                    'Description5',
                    'pay_later',
                    new SeQuraCost(0, 0, 0, 0),
                    new \DateTime('0022-02-22T22:36:44Z'),
                    new \DateTime('0022-02-22T22:36:44Z'),
                    null,
                    ''
                )
            ]
        );

        // act
        $widget = $this->widgetSettingsService->getAvailableWidgetForCartPage('FR', 'FR');

        // assert
        self::assertNotNull($widget);
        self::assertEquals('i1', $widget->getProduct());
        self::assertEquals('campaign1', $widget->getCampaign());
        self::assertEquals('location2', $widget->getDest());
        self::assertEquals('location1', $widget->getPriceSelector());
        self::assertEquals('configTest', $widget->getTheme());
        self::assertEquals('0', $widget->getReverse());
    }

    /**
     * @return void
     *
     * @throws HttpRequestException
     * @throws InvalidEnvironmentException
     * @throws PaymentMethodNotFoundException
     */
    public function testGetAvailableMiniWidgetNoWidgetSettings(): void
    {
        //arrange
        $this->countryConfigService->saveCountryConfiguration(
            [
                new CountryConfiguration('ES', 'merchantES'),
                new CountryConfiguration('FR', 'merchantFR'),
                new CountryConfiguration('IT', 'merchantIT'),
            ]
        );
        $this->connectionService->saveConnectionData(
            new ConnectionData(
                'live',
                'test',
                new AuthorizationCredentials('test_username', 'test_password')
            )
        );

        // act
        $widget = $this->widgetSettingsService->getAvailableMiniWidget('FR', 'FR');

        // assert
        self::assertNull($widget);
    }

    /**
     * @return void
     *
     * @throws HttpRequestException
     * @throws InvalidEnvironmentException
     * @throws PaymentMethodNotFoundException
     * @throws Exception
     */
    public function testGetAvailableMiniWidgetNoWidgetSettingsForListing(): void
    {
        //arrange
        $this->countryConfigService->saveCountryConfiguration(
            [
                new CountryConfiguration('ES', 'merchantES'),
                new CountryConfiguration('FR', 'merchantFR'),
                new CountryConfiguration('IT', 'merchantIT'),
            ]
        );
        $this->connectionService->saveConnectionData(
            new ConnectionData(
                'live',
                'test',
                new AuthorizationCredentials('test_username', 'test_password')
            )
        );
        $this->widgetSettingsRepository->setWidgetSettings(new WidgetSettingsModel(true));

        // act
        $widget = $this->widgetSettingsService->getAvailableMiniWidget('FR', 'FR');

        // assert
        self::assertNull($widget);
    }

    /**
     * @return void
     *
     * @throws HttpRequestException
     * @throws InvalidEnvironmentException
     * @throws PaymentMethodNotFoundException
     * @throws Exception
     */
    public function testGetAvailableMiniWidgetNoPaymentMethodFound(): void
    {
        //arrange
        $this->countryConfigService->saveCountryConfiguration(
            [
                new CountryConfiguration('ES', 'merchantES'),
                new CountryConfiguration('FR', 'merchantFR'),
                new CountryConfiguration('IT', 'merchantIT'),
            ]
        );
        $this->connectionService->saveConnectionData(
            new ConnectionData(
                'live',
                'test',
                new AuthorizationCredentials('test_username', 'test_password')
            )
        );
        $this->widgetSettingsRepository->setWidgetSettings(new WidgetSettingsModel(
            true,
            false,
            false,
            false,
            '',
            null,
            null,
            new WidgetSelectorSettings('test', 'test')
        ));

        // act
        $widget = $this->widgetSettingsService->getAvailableMiniWidget('FR', 'FR');

        // assert
        self::assertNull($widget);
    }

    /**
     * @return void
     *
     * @throws HttpRequestException
     * @throws InvalidEnvironmentException
     * @throws PaymentMethodNotFoundException
     * @throws Exception
     */
    public function testGetAvailableMiniWidget(): void
    {
        //arrange
        $this->countryConfigService->saveCountryConfiguration(
            [
                new CountryConfiguration('ES', 'merchantES'),
                new CountryConfiguration('FR', 'merchantFR'),
                new CountryConfiguration('IT', 'merchantIT'),
            ]
        );
        $this->connectionService->saveConnectionData(
            new ConnectionData(
                'live',
                'test',
                new AuthorizationCredentials('test_username', 'test_password')
            )
        );
        $this->widgetSettingsRepository->setWidgetSettings(new WidgetSettingsModel(
            true,
            false,
            true,
            false,
            'configTest',
            null,
            null,
            new WidgetSelectorSettings('test1', 'test2', 'i1')
        ));
        $this->paymentMethodsService->setMockPaymentMethods(
            [
                new SeQuraPaymentMethod(
                    'i1',
                    'Payment2',
                    'Description2',
                    'part_payment',
                    new SeQuraCost(0, 0, 0, 0),
                    new \DateTime('0022-02-22T22:36:44Z'),
                    new \DateTime('0022-02-22T22:36:44Z'),
                    'campaign1',
                    ''
                ),
                new SeQuraPaymentMethod(
                    'pp3',
                    'Payment3',
                    'Description3',
                    'pay_later',
                    new SeQuraCost(0, 0, 0, 0),
                    new \DateTime('0022-02-22T22:36:44Z'),
                    new \DateTime('0022-02-22T22:36:44Z'),
                    null,
                    ''
                ),
                new SeQuraPaymentMethod(
                    'payment7',
                    'Payment7',
                    'Description7',
                    'pay_later',
                    new SeQuraCost(0, 0, 0, 0),
                    new \DateTime('0022-02-22T22:36:44Z'),
                    new \DateTime('0022-02-22T22:36:44Z'),
                    null,
                    ''
                ),
                new SeQuraPaymentMethod(
                    'payment5',
                    'Payment5',
                    'Description5',
                    'pay_later',
                    new SeQuraCost(0, 0, 0, 0),
                    new \DateTime('0022-02-22T22:36:44Z'),
                    new \DateTime('0022-02-22T22:36:44Z'),
                    null,
                    ''
                )
            ]
        );

        // act
        $widget = $this->widgetSettingsService->getAvailableMiniWidget('FR', 'FR');

        // assert
        self::assertNotNull($widget);
        self::assertEquals('i1', $widget->getProduct());
        self::assertEquals('campaign1', $widget->getCampaign());
        self::assertEquals('test1', $widget->getPriceSelector());
        self::assertEquals('test2', $widget->getDest());
        self::assertEquals('configTest', $widget->getTheme());
        self::assertEquals('0', $widget->getReverse());
    }

    /**
     * @return void
     *
     * @throws HttpRequestException
     * @throws InvalidEnvironmentException
     * @throws PaymentMethodNotFoundException
     */
    public function testGetAvailableWidgetsForProductPageNoWidgetSettings(): void
    {
        //arrange
        $this->countryConfigService->saveCountryConfiguration(
            [
                new CountryConfiguration('ES', 'merchantES'),
                new CountryConfiguration('FR', 'merchantFR'),
                new CountryConfiguration('IT', 'merchantIT'),
            ]
        );
        $this->connectionService->saveConnectionData(
            new ConnectionData(
                'live',
                'test',
                new AuthorizationCredentials('test_username', 'test_password')
            )
        );

        // act
        $widgets = $this->widgetSettingsService->getAvailableWidgetsForProductPage('FR', 'FR');

        // assert
        self::assertEmpty($widgets);
    }

    /**
     * @return void
     *
     * @throws HttpRequestException
     * @throws InvalidEnvironmentException
     * @throws PaymentMethodNotFoundException
     * @throws Exception
     */
    public function testGetAvailableWidgetsForProductPageNoWidgetSettingsForProduct(): void
    {
        //arrange
        $this->countryConfigService->saveCountryConfiguration(
            [
                new CountryConfiguration('ES', 'merchantES'),
                new CountryConfiguration('FR', 'merchantFR'),
                new CountryConfiguration('IT', 'merchantIT'),
            ]
        );
        $this->connectionService->saveConnectionData(
            new ConnectionData(
                'live',
                'test',
                new AuthorizationCredentials('test_username', 'test_password')
            )
        );
        $this->widgetSettingsRepository->setWidgetSettings(new WidgetSettingsModel(true));

        // act
        $widgets = $this->widgetSettingsService->getAvailableWidgetsForProductPage('FR', 'FR');

        // assert
        self::assertEmpty($widgets);
    }

    /**
     * @return void
     *
     * @throws HttpRequestException
     * @throws InvalidEnvironmentException
     * @throws PaymentMethodNotFoundException
     * @throws Exception
     */
    public function testGetAvailableWidgetsForProductPageNoSupportedPaymentMethods(): void
    {
        //arrange
        $this->countryConfigService->saveCountryConfiguration(
            [
                new CountryConfiguration('ES', 'merchantES'),
                new CountryConfiguration('FR', 'merchantFR'),
                new CountryConfiguration('IT', 'merchantIT'),
            ]
        );
        $this->connectionService->saveConnectionData(
            new ConnectionData(
                'live',
                'test',
                new AuthorizationCredentials('test_username', 'test_password')
            )
        );
        $this->widgetSettingsRepository->setWidgetSettings(new WidgetSettingsModel(
            true,
            false,
            false,
            false,
            '',
            null,
            new WidgetSelectorSettings(
                'testPriceSelector',
                'testLocationSelector',
                'i1',
                '',
                '',
                [
                        new CustomWidgetsSettings('customLocationSelector', 'i1', true, 'style'),
                        new CustomWidgetsSettings('customLocationSelector', 'i2', true, 'style')
                    ]
            )
        ));

        // act
        $widgets = $this->widgetSettingsService->getAvailableWidgetsForProductPage('FR', 'FR');

        // assert
        self::assertEmpty($widgets);
    }

    /**
     * @return void
     *
     * @throws HttpRequestException
     * @throws InvalidEnvironmentException
     * @throws PaymentMethodNotFoundException
     * @throws Exception
     */
    public function testGetAvailableWidgetsForProductPage(): void
    {
        //arrange
        $this->countryConfigService->saveCountryConfiguration(
            [
                new CountryConfiguration('ES', 'merchantES'),
                new CountryConfiguration('FR', 'merchantFR'),
                new CountryConfiguration('IT', 'merchantIT'),
            ]
        );
        $this->connectionService->saveConnectionData(
            new ConnectionData(
                'live',
                'test',
                new AuthorizationCredentials('test_username', 'test_password')
            )
        );
        $this->widgetSettingsRepository->setWidgetSettings(new WidgetSettingsModel(
            true,
            true,
            false,
            false,
            '',
            new WidgetSelectorSettings(
                'testPriceSelector',
                'testLocationSelector',
                'i1',
                'altPriceSelector',
                'altPriceTriggerSelector',
                [
                        new CustomWidgetsSettings('customLocationSelector', 'i1', true, 'style'),
                        new CustomWidgetsSettings('customLocationSelector', 'pp3', false, 'style'),
                        new CustomWidgetsSettings('customLocationSelector', 'pp4', true, 'style'),
                    ]
            )
        ));
        $this->paymentMethodsService->setMockPaymentMethods(
            [
                new SeQuraPaymentMethod(
                    'i1',
                    'Payment2',
                    'Description2',
                    'pay_later',
                    new SeQuraCost(0, 0, 0, 0),
                    new \DateTime('0022-02-22T22:36:44Z'),
                    new \DateTime('0022-02-22T22:36:44Z'),
                    'campaign1',
                    ''
                ),
                new SeQuraPaymentMethod(
                    'pp3',
                    'Payment3',
                    'Description3',
                    'pay_later',
                    new SeQuraCost(0, 0, 0, 0),
                    new \DateTime('0022-02-22T22:36:44Z'),
                    new \DateTime('0022-02-22T22:36:44Z'),
                    null,
                    ''
                ),
                new SeQuraPaymentMethod(
                    'pp4',
                    'Payment7',
                    'Description7',
                    'pay_later',
                    new SeQuraCost(0, 0, 0, 0),
                    new \DateTime('0022-02-22T22:36:44Z'),
                    new \DateTime('0022-02-22T22:36:44Z'),
                    null,
                    '',
                    '',
                    '',
                    '',
                    10,
                    20
                ),
                new SeQuraPaymentMethod(
                    'payment5',
                    'Payment5',
                    'Description5',
                    'pay3',
                    new SeQuraCost(0, 0, 0, 0),
                    new \DateTime('0022-02-22T22:36:44Z'),
                    new \DateTime('0022-02-22T22:36:44Z'),
                    null,
                    ''
                )
            ]
        );

        // act
        $widgets = $this->widgetSettingsService->getAvailableWidgetsForProductPage('FR', 'FR');

        // assert
        self::assertNotEmpty($widgets);
        self::assertCount(2, $widgets);

        /** @var Widget $widget */
        $widget = $widgets[0];

        self::assertEquals('i1', $widget->getProduct());
        self::assertEquals('testPriceSelector', $widget->getPriceSelector());
        self::assertEquals('customLocationSelector', $widget->getDest());
        self::assertEquals('style', $widget->getTheme());
        self::assertEquals('0', $widget->getReverse());
        self::assertEquals(0, $widget->getMinAmount());
        self::assertEquals(0, $widget->getMaxAmount());
        self::assertEquals('altPriceSelector', $widget->getAltPriceSelector());
        self::assertEquals('altPriceTriggerSelector', $widget->getAltTriggerSelector());

        /** @var Widget $widget */
        $widget = $widgets[1];

        self::assertEquals('pp4', $widget->getProduct());
        self::assertEquals('testPriceSelector', $widget->getPriceSelector());
        self::assertEquals('customLocationSelector', $widget->getDest());
        self::assertEquals('style', $widget->getTheme());
        self::assertEquals('0', $widget->getReverse());
        self::assertEquals(10, $widget->getMinAmount());
        self::assertEquals(20, $widget->getMaxAmount());
        self::assertEquals('altPriceSelector', $widget->getAltPriceSelector());
        self::assertEquals('altPriceTriggerSelector', $widget->getAltTriggerSelector());
    }
}
