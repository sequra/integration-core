<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\PromotionalWidgets\Services;

use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidEnvironmentException;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\AuthorizationCredentials;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use SeQura\Core\BusinessLogic\Domain\Connection\ProxyContracts\ConnectionProxyInterface;
use SeQura\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Models\CountryConfiguration;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\RepositoryContracts\CountryConfigurationRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Services\CountryConfigurationService;
use SeQura\Core\BusinessLogic\Domain\Integration\SellingCountries\SellingCountriesServiceInterface;
use SeQura\Core\BusinessLogic\Domain\Merchant\ProxyContracts\MerchantProxyInterface;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Exceptions\PaymentMethodNotFoundException;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Models\SeQuraCost;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Models\SeQuraPaymentMethod;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\RepositoryContracts\PaymentMethodRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Services\PaymentMethodsService;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\RepositoryContracts\WidgetSettingsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Services\WidgetSettingsService;
use SeQura\Core\Infrastructure\Http\Exceptions\HttpRequestException;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockConnectionService;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockCountryConfigurationService;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockPaymentMethodService;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockSellingCountriesService;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockWidgetProxy;
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
     * @var MockConnectionService
     */
    private $connectionService;

    /**
     * @var MockWidgetProxy
     */
    private $widgetProxy;

    /**
     * @var WidgetSettingsService
     */
    private $widgetSettingsService;

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

        $this->countryConfigService = new MockCountryConfigurationService(
            TestServiceRegister::getService(CountryConfigurationRepositoryInterface::class),
            TestServiceRegister::getService(SellingCountriesServiceInterface::class)
        );

        TestServiceRegister::registerService(CountryConfigurationService::class, function () {
            return $this->countryConfigService;
        });

        $this->connectionService = new MockConnectionService(
            TestServiceRegister::getService(ConnectionProxyInterface::class)
        );

        TestServiceRegister::registerService(ConnectionService::class, function () {
            return $this->connectionService;
        });

        $this->widgetProxy = new MockWidgetProxy();

        TestServiceRegister::registerService(MerchantProxyInterface::class, function () {
            return $this->widgetProxy;
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
    public function testGetMerchantIdFromShippingCountry(): void
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
    public function testGetMerchantIdFromCurrentCountry(): void
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
    public function testGetMerchantIdNoId(): void
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
    public function testGetProductsNoProducts(): void
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
    public function testGetProducts(): void
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
        $this->paymentMethodsService->setMockProducts(['i1', 'pp5', 'pp3','payment7', 'payment5']);

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
        self::assertEquals(['i1', 'pp5', 'pp3'], $widgetInitialize->getProducts());
    }
}
