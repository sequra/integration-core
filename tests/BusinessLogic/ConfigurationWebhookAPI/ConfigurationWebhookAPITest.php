<?php

namespace SeQura\Core\Tests\BusinessLogic\ConfigurationWebhookAPI;

use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\ConfigurationWebhookAPI;
use SeQura\Core\BusinessLogic\Domain\AdvancedSettings\Models\AdvancedSettings;
use SeQura\Core\BusinessLogic\Domain\AdvancedSettings\Services\AdvancedSettingsService;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidEnvironmentException;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\AuthorizationCredentials;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use SeQura\Core\BusinessLogic\Domain\Connection\RepositoryContracts\ConnectionDataRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use SeQura\Core\BusinessLogic\Domain\Connection\Services\CredentialsService;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Models\SellingCountry;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\RepositoryContracts\CountryConfigurationRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Services\CountryConfigurationService;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Services\SellingCountriesService;
use SeQura\Core\BusinessLogic\Domain\Deployments\Services\DeploymentsService;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Exceptions\EmptyCategoryParameterException;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Models\Category;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Models\GeneralSettings;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\RepositoryContracts\GeneralSettingsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Services\CategoryService;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Services\GeneralSettingsService;
use SeQura\Core\BusinessLogic\Domain\HMAC\HMAC;
use SeQura\Core\BusinessLogic\Domain\Integration\Category\CategoryServiceInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\Log\LogServiceInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\Product\ProductServiceInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\PromotionalWidgets\MiniWidgetMessagesProviderInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\PromotionalWidgets\WidgetConfiguratorInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\SellingCountries\SellingCountriesServiceInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\StoreInfo\StoreInfoServiceInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\StoreIntegration\StoreIntegrationServiceInterface;
use SeQura\Core\BusinessLogic\Domain\Log\Model\Log;
use SeQura\Core\BusinessLogic\Domain\Merchant\ProxyContracts\MerchantProxyInterface;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\OrderStatus\Models\OrderStatus;
use SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\Models\OrderStatusMapping;
use SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\RepositoryContracts\OrderStatusSettingsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\Services\OrderStatusSettingsService;
use SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\Services\ShopOrderStatusesService;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Models\SeQuraCost;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Models\SeQuraPaymentMethod;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\RepositoryContracts\PaymentMethodRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Services\PaymentMethodsService;
use SeQura\Core\BusinessLogic\Domain\Product\Model\ShopProduct;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\CustomWidgetsSettings;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\WidgetSelectorSettings;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\WidgetSettings;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\RepositoryContracts\WidgetSettingsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Services\WidgetSettingsService;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Services\StoreIntegrationService;
use SeQura\Core\BusinessLogic\Domain\Stores\Models\StoreInfo;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockAdvancedSettingsRepository;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockAdvancedSettingsService;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockCategoryService;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockConnectionService;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockCoreSellingCountriesService;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockCountryConfigurationService;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockDomainShopOrderStatusesService;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockGeneralSettingsService;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockIntegrationStoreIntegrationService;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockLogService;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockOrderStatusSettingsService;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockPaymentMethodService;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockProductService;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockSellingCountriesService;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockShopOrderStatusesService;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockStoreInfoService;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockStoreIntegrationProxy;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockStoreIntegrationService;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockWidgetSettingsService;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class ConfigurationWebhookAPITest.
 *
 * @package ConfigurationWebhookAPI
 */
class ConfigurationWebhookAPITest extends BaseTestCase
{
    /**
     * @var MockConnectionService $connectionService
     */
    private $connectionService;

    /**
     * @var MockStoreIntegrationService $storeIntegrationService
     */
    private $storeIntegrationService;

    /**
     * @var MockIntegrationStoreIntegrationService $integrationStoreIntegrationService
     */
    private $integrationStoreIntegrationService;

    /**
     * @var MockCategoryService $categoryService
     */
    private $integrationCategoryService;

    /**
     * @var CategoryService $categoryService
     */
    private $categoryService;

    /**
     * @var MockStoreInfoService $storeInfoService
     */
    private $storeInfoService;

    /**
     * @var MockLogService $logService
     */
    private $logService;

    /**
     * @var MockProductService $productService
     */
    private $productService;

    /**
     * @var MockCategoryService $shopCategoryService
     */
    private $shopCategoryService;

    /**
     * @var MockSellingCountriesService $sellingCountriesService
     */
    private $sellingCountriesService;

    /**
     * @var MockWidgetSettingsService $widgetSettingsService
     */
    private $widgetSettingsService;

    /**
     * @var MockPaymentMethodService $paymentMethodsService
     */
    private $paymentMethodsService;

    /**
     * @var MockGeneralSettingsService $generalSettingsService
     */
    private $generalSettingsService;

    /**
     * @var MockDomainShopOrderStatusesService $shopOrderStatusService
     */
    private $shopOrderStatusService;

    /**
     * @var MockOrderStatusSettingsService $orderStatusSettingsService
     */
    private $orderStatusSettingsService;

    /**
     * @var MockCountryConfigurationService $countryConfigurationService
     */
    private $countryConfigurationService;

    /**
     * @var MockCoreSellingCountriesService $domainSellingCountriesService
     */
    private $domainSellingCountriesService;

    /**
     * @var AdvancedSettingsService $advancedSettingsService
     */
    private $advancedSettingsService;

    /**
     * @return void
     *
     * @throws RepositoryClassException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->connectionService = new MockConnectionService(
            TestServiceRegister::getService(ConnectionDataRepositoryInterface::class),
            TestServiceRegister::getService(CredentialsService::class),
            TestServiceRegister::getService(StoreIntegrationService::class)
        );

        $this->integrationStoreIntegrationService = new MockIntegrationStoreIntegrationService();

        TestServiceRegister::registerService(StoreIntegrationServiceInterface::class, function () {
            return $this->integrationStoreIntegrationService;
        });

        $this->storeIntegrationService = new MockStoreIntegrationService(
            $this->integrationStoreIntegrationService,
            new MockStoreIntegrationProxy()
        );

        TestServiceRegister::registerService(StoreIntegrationService::class, function () {
            return $this->storeIntegrationService;
        });

        TestServiceRegister::registerService(ConnectionService::class, function () {
            return $this->connectionService;
        });

        $this->integrationCategoryService = new MockCategoryService();


        TestServiceRegister::registerService(CategoryServiceInterface::class, function () {
            return $this->integrationCategoryService;
        });

        $this->categoryService = new CategoryService($this->integrationCategoryService);

        TestServiceRegister::registerService(CategoryService::class, function () {
            return $this->categoryService;
        });

        $this->storeInfoService = new MockStoreInfoService();

        TestServiceRegister::registerService(StoreInfoServiceInterface::class, function () {
            return $this->storeInfoService;
        });

        $this->logService = new MockLogService();

        TestServiceRegister::registerService(LogServiceInterface::class, function () {
            return $this->logService;
        });

        $this->productService = new MockProductService();

        TestServiceRegister::registerService(ProductServiceInterface::class, function () {
            return $this->productService;
        });

        $this->shopCategoryService = new MockCategoryService();

        TestServiceRegister::registerService(CategoryServiceInterface::class, function () {
            return $this->shopCategoryService;
        });

        $this->sellingCountriesService = new MockSellingCountriesService();

        TestServiceRegister::registerService(SellingCountriesServiceInterface::class, function () {
            return $this->sellingCountriesService;
        });

        $this->widgetSettingsService = new MockWidgetSettingsService(
            TestServiceRegister::getService(WidgetSettingsRepositoryInterface::class),
            TestServiceRegister::getService(PaymentMethodsService::class),
            TestServiceRegister::getService(CredentialsService::class),
            TestServiceRegister::getService(ConnectionService::class),
            TestServiceRegister::getService(WidgetConfiguratorInterface::class),
            TestServiceRegister::getService(MiniWidgetMessagesProviderInterface::class),
            TestServiceRegister::getService(DeploymentsService::class)
        );

        TestServiceRegister::registerService(WidgetSettingsService::class, function () {
            return $this->widgetSettingsService;
        });

        $this->paymentMethodsService = new MockPaymentMethodService(
            TestServiceRegister::getService(MerchantProxyInterface::class),
            TestServiceRegister::getService(PaymentMethodRepositoryInterface::class),
            TestServiceRegister::getService(CountryConfigurationService::class)
        );

        TestServiceRegister::registerService(PaymentMethodsService::class, function () {
            return $this->paymentMethodsService;
        });

        $this->generalSettingsService = new MockGeneralSettingsService(
            TestServiceRegister::getService(GeneralSettingsRepositoryInterface::class),
            TestServiceRegister::getService(ConnectionService::class),
            TestServiceRegister::getService(CountryConfigurationService::class)
        );

        TestServiceRegister::registerService(GeneralSettingsService::class, function () {
            return $this->generalSettingsService;
        });

        $this->shopOrderStatusService = new MockDomainShopOrderStatusesService(
            new MockShopOrderStatusesService()
        );

        TestServiceRegister::registerService(ShopOrderStatusesService::class, function () {
            return $this->shopOrderStatusService;
        });

        $this->orderStatusSettingsService = new MockOrderStatusSettingsService(
            TestServiceRegister::getService(OrderStatusSettingsRepositoryInterface::class),
            new MockShopOrderStatusesService()
        );

        TestServiceRegister::registerService(OrderStatusSettingsService::class, function () {
            return $this->orderStatusSettingsService;
        });

        $this->domainSellingCountriesService = new MockCoreSellingCountriesService(
            TestServiceRegister::getService(SellingCountriesServiceInterface::class),
            TestServiceRegister::getService(ConnectionService::class)
        );

        TestServiceRegister::registerService(
            SellingCountriesService::class,
            function () {
                return $this->domainSellingCountriesService;
            }
        );

        $this->countryConfigurationService = new MockCountryConfigurationService(
            TestServiceRegister::getService(CountryConfigurationRepositoryInterface::class),
            TestServiceRegister::getService(SellingCountriesService::class)
        );

        TestServiceRegister::registerService(CountryConfigurationService::class, function () {
            return $this->countryConfigurationService;
        });

        $this->advancedSettingsService = new MockAdvancedSettingsService(
            new MockAdvancedSettingsRepository()
        );

        TestServiceRegister::registerService(AdvancedSettingsService::class, function () {
            return $this->advancedSettingsService;
        });
    }

    /**
     * @return void
     *
     * @throws InvalidEnvironmentException
     */
    public function testTopicMissingResponse(): void
    {
        //Arrange

        //Act
        $response = ConfigurationWebhookAPI::configurationHandler()->handleRequest(
            'merchant1',
            'test',
            [
                'page' => 1,
                'limit' => 5,
                'search' => '',
            ]
        );

        //Assert
        self::assertFalse($response->isSuccessful());
        self::assertEquals([
            'success' => false,
            'error' => 'Topic field is required in the webhook payload.',
            'errorCode' => 'TOPIC_MISSING'
        ], $response->toArray());
    }

    /**
     * @return void
     *
     * @throws InvalidEnvironmentException
     */
    public function testInvalidValidWebhookSignature(): void
    {
        //Arrange
        $connectionData = new ConnectionData(
            'sandbox',
            'merchant1',
            'sequra',
            new AuthorizationCredentials('username', 'password'),
            '1'
        );
        $this->connectionService->saveConnectionData($connectionData);

        //Act
        $response = ConfigurationWebhookAPI::configurationHandler()->handleRequest(
            'merchant1',
            'testFail',
            [
                'topic' => 'get-shop-categories',
                'page' => 1,
                'limit' => 5,
                'search' => '',
            ]
        );

        //Assert
        self::assertFalse($response->isSuccessful());
        self::assertEquals('Webhook signature validation failed.', $response->toArray()['errorMessage']);
    }

    /**
     * @return void
     *
     * @throws InvalidEnvironmentException
     */
    public function testValidWebhook(): void
    {
        //Arrange
        $connectionData = new ConnectionData(
            'sandbox',
            'merchant1',
            'sequra',
            new AuthorizationCredentials('username', 'password'),
            '1'
        );
        $this->connectionService->saveConnectionData($connectionData);
        $signaturePayload = [
            StoreContext::getInstance()->getStoreId(),
            $this->integrationStoreIntegrationService->getWebhookUrl()->getPath(),
            $connectionData->getAuthorizationCredentials()->getUsername(),
            $connectionData->getMerchantId()
        ];
        $signature = HMAC::generateHMAC(
            $signaturePayload,
            $connectionData->getAuthorizationCredentials()->getPassword()
        );

        //Act
        $response = ConfigurationWebhookAPI::configurationHandler()->handleRequest(
            'merchant1',
            $signature,
            [
                'topic' => 'get-shop-categories',
                'page' => 1,
                'limit' => 5,
                'search' => '',
            ]
        );

        //Assert
        self::assertTrue($response->isSuccessful());
    }

    /**
     * @return void
     *
     * @throws InvalidEnvironmentException
     */
    public function testUnknownEpicError(): void
    {
        //Arrange
        $connectionData = new ConnectionData(
            'sandbox',
            'merchant1',
            'sequra',
            new AuthorizationCredentials('username', 'password'),
            '1'
        );
        $this->connectionService->saveConnectionData($connectionData);
        $signaturePayload = [
            StoreContext::getInstance()->getStoreId(),
            $this->integrationStoreIntegrationService->getWebhookUrl()->getPath(),
            $connectionData->getAuthorizationCredentials()->getUsername(),
            $connectionData->getMerchantId()
        ];
        $signature = HMAC::generateHMAC(
            $signaturePayload,
            $connectionData->getAuthorizationCredentials()->getPassword()
        );

        //Act
        $response = ConfigurationWebhookAPI::configurationHandler()->handleRequest(
            'merchant1',
            $signature,
            [
                'topic' => 'get-payment-data',
                'page' => 1,
                'limit' => 5,
                'search' => '',
            ]
        );

        //Assert
        self::assertFalse($response->isSuccessful());
        self::assertEquals([
            'success' => false,
            'error' => 'Unknown or unsupported topic: get-payment-data',
            'errorCode' => 'UNKNOWN_TOPIC'
        ], $response->toArray());
    }

    /**
     * @return void
     *
     * @throws InvalidEnvironmentException
     */
    public function testStoreInfoResponse(): void
    {
        //Arrange
        $connectionData = new ConnectionData(
            'sandbox',
            'merchant1',
            'sequra',
            new AuthorizationCredentials('username', 'password'),
            '1'
        );
        $this->connectionService->saveConnectionData($connectionData);
        $signaturePayload = [
            StoreContext::getInstance()->getStoreId(),
            $this->integrationStoreIntegrationService->getWebhookUrl()->getPath(),
            $connectionData->getAuthorizationCredentials()->getUsername(),
            $connectionData->getMerchantId()
        ];
        $signature = HMAC::generateHMAC(
            $signaturePayload,
            $connectionData->getAuthorizationCredentials()->getPassword()
        );

        $this->storeInfoService->setMockStoreInfo(
            new StoreInfo(
                'Test Store',
                'https://test.com',
                'Test Platform',
                'v1.0.0',
                'v2.1',
                '8.4',
                'mysql',
                'linux',
                [
                    'plugin1',
                    'plugin2',
                    'plugin3'
                ]
            )
        );

        //Act
        $response = ConfigurationWebhookAPI::configurationHandler()->handleRequest(
            'merchant1',
            $signature,
            [
                'topic' => 'get-store-info'
            ]
        );

        //Assert
        self::assertTrue($response->isSuccessful());
        self::assertEquals([
            'store_name' => 'Test Store',
            'store_url' => 'https://test.com',
            'platform' => 'Test Platform',
            'platform_version' => 'v1.0.0',
            'plugin_version' => 'v2.1',
            'php_version' => '8.4',
            'db' => 'mysql',
            'os' => 'linux',
            'plugins' => ['plugin1', 'plugin2', 'plugin3']
        ], $response->toArray());
    }

    /**
     * @return void
     *
     * @throws InvalidEnvironmentException
     */
    public function testGetLogResponse(): void
    {
        //Arrange
        $connectionData = new ConnectionData(
            'sandbox',
            'merchant1',
            'sequra',
            new AuthorizationCredentials('username', 'password'),
            '1'
        );
        $this->connectionService->saveConnectionData($connectionData);
        $signaturePayload = [
            StoreContext::getInstance()->getStoreId(),
            $this->integrationStoreIntegrationService->getWebhookUrl()->getPath(),
            $connectionData->getAuthorizationCredentials()->getUsername(),
            $connectionData->getMerchantId()
        ];
        $signature = HMAC::generateHMAC(
            $signaturePayload,
            $connectionData->getAuthorizationCredentials()->getPassword()
        );

        $this->storeInfoService->setMockStoreInfo(
            new StoreInfo(
                'Test Store',
                'https://test.com',
                'Test Platform',
                'v1.0.0',
                'v2.1',
                '8.4',
                'mysql',
                'linux',
                [
                    'plugin1',
                    'plugin2',
                    'plugin3'
                ]
            )
        );

        $this->logService->setMockLog(new Log([
            "DEBUG\t2026-01-23 15:15:55\tSending http request to https:\/\/live.sequrapi.com\/deployments\t [{\"name\":\"Type\",\"value\":\"GET\"},{\"name\":\"Endpoint\",\"value\":\"https:\/\/live.sequrapi.com\/deployments\"},{\"name\":\"Headers\",\"value\":{\"Content-Type\":\"Content-Type: application\/json\",\"Accept\":\"Accept: application\/json\"}},{\"name\":\"Content\",\"value\":[]}]\r\n",
            "INFO\t2026-01-23 15:15:55\tSeQura\\WC\\Controllers\\Hooks\\I18n\\I18n_Controller::load_text_domain() - Hook executed\t{\"dummyAttrItems\":[\"dummyItem1\",\"dummyItem2\",\"dummyItem3\"]}\r\n",
            "WARNING\t2026-01-23 15:15:55\tSeQura\\WC\\Controllers\\Hooks\\Payment\\Payment_Controller::register_gateway_gutenberg_block() - Hook executed\t\r\n"
        ]));

        //Act
        $response = ConfigurationWebhookAPI::configurationHandler()->handleRequest(
            'merchant1',
            $signature,
            [
                'topic' => 'get-log-content'
            ]
        );

        //Assert
        self::assertTrue($response->isSuccessful());
        self::assertEquals([
            "DEBUG\t2026-01-23 15:15:55\tSending http request to https:\/\/live.sequrapi.com\/deployments\t [{\"name\":\"Type\",\"value\":\"GET\"},{\"name\":\"Endpoint\",\"value\":\"https:\/\/live.sequrapi.com\/deployments\"},{\"name\":\"Headers\",\"value\":{\"Content-Type\":\"Content-Type: application\/json\",\"Accept\":\"Accept: application\/json\"}},{\"name\":\"Content\",\"value\":[]}]\r\n",
            "INFO\t2026-01-23 15:15:55\tSeQura\\WC\\Controllers\\Hooks\\I18n\\I18n_Controller::load_text_domain() - Hook executed\t{\"dummyAttrItems\":[\"dummyItem1\",\"dummyItem2\",\"dummyItem3\"]}\r\n",
            "WARNING\t2026-01-23 15:15:55\tSeQura\\WC\\Controllers\\Hooks\\Payment\\Payment_Controller::register_gateway_gutenberg_block() - Hook executed\t\r\n",
        ], $response->toArray());
    }

    /**
     * @return void
     *
     * @throws InvalidEnvironmentException
     */
    public function testRemoveLogResponse(): void
    {
        //Arrange
        $connectionData = new ConnectionData(
            'sandbox',
            'merchant1',
            'sequra',
            new AuthorizationCredentials('username', 'password'),
            '1'
        );
        $this->connectionService->saveConnectionData($connectionData);
        $signaturePayload = [
            StoreContext::getInstance()->getStoreId(),
            $this->integrationStoreIntegrationService->getWebhookUrl()->getPath(),
            $connectionData->getAuthorizationCredentials()->getUsername(),
            $connectionData->getMerchantId()
        ];
        $signature = HMAC::generateHMAC(
            $signaturePayload,
            $connectionData->getAuthorizationCredentials()->getPassword()
        );

        $this->storeInfoService->setMockStoreInfo(
            new StoreInfo(
                'Test Store',
                'https://test.com',
                'Test Platform',
                'v1.0.0',
                'v2.1',
                '8.4',
                'mysql',
                'linux',
                [
                    'plugin1',
                    'plugin2',
                    'plugin3'
                ]
            )
        );

        $this->logService->setMockLog(new Log([
            "DEBUG\t2026-01-23 15:15:55\tSending http request to https:\/\/live.sequrapi.com\/deployments\t [{\"name\":\"Type\",\"value\":\"GET\"},{\"name\":\"Endpoint\",\"value\":\"https:\/\/live.sequrapi.com\/deployments\"},{\"name\":\"Headers\",\"value\":{\"Content-Type\":\"Content-Type: application\/json\",\"Accept\":\"Accept: application\/json\"}},{\"name\":\"Content\",\"value\":[]}]\r\n",
            "INFO\t2026-01-23 15:15:55\tSeQura\\WC\\Controllers\\Hooks\\I18n\\I18n_Controller::load_text_domain() - Hook executed\t{\"dummyAttrItems\":[\"dummyItem1\",\"dummyItem2\",\"dummyItem3\"]}\r\n",
            "WARNING\t2026-01-23 15:15:55\tSeQura\\WC\\Controllers\\Hooks\\Payment\\Payment_Controller::register_gateway_gutenberg_block() - Hook executed\t\r\n"
        ]));

        //Act
        $response = ConfigurationWebhookAPI::configurationHandler()->handleRequest(
            'merchant1',
            $signature,
            [
                'topic' => 'remove-log-content'
            ]
        );

        //Assert
        self::assertTrue($response->isSuccessful());
        self::assertEquals([
        ], $this->logService->getLog()->toArray());
    }

    /**
     * @return void
     *
     * @throws InvalidEnvironmentException
     */
    public function testGetStoreProductsResponse(): void
    {
        //Arrange
        $connectionData = new ConnectionData(
            'sandbox',
            'merchant1',
            'sequra',
            new AuthorizationCredentials('username', 'password'),
            '1'
        );
        $this->connectionService->saveConnectionData($connectionData);
        $signaturePayload = [
            StoreContext::getInstance()->getStoreId(),
            $this->integrationStoreIntegrationService->getWebhookUrl()->getPath(),
            $connectionData->getAuthorizationCredentials()->getUsername(),
            $connectionData->getMerchantId()
        ];
        $signature = HMAC::generateHMAC(
            $signaturePayload,
            $connectionData->getAuthorizationCredentials()->getPassword()
        );

        $this->productService->setMockShopProducts(
            [
                new ShopProduct(1, 11, 'Test1'),
                new ShopProduct(2, 22, 'Test2'),
                new ShopProduct(3, 33, 'Test3')
            ]
        );

        //Act
        $response = ConfigurationWebhookAPI::configurationHandler()->handleRequest(
            'merchant1',
            $signature,
            [
                'topic' => 'get-shop-products',
                'page' => 1,
                'limit' => 5,
                'search' => '',
            ]
        );

        //Assert
        self::assertTrue($response->isSuccessful());
        self::assertEquals([
            [
                'id' => 1,
                'sku' => 11,
                'name' => 'Test1'
            ],
            [
                'id' => 2,
                'sku' => 22,
                'name' => 'Test2'
            ],
            [
                'id' => 3,
                'sku' => 33,
                'name' => 'Test3'
            ]
        ], $response->toArray());
    }

    /**
     * @return void
     *
     * @throws InvalidEnvironmentException
     * @throws EmptyCategoryParameterException
     */
    public function testGetShopCategoriesResponse(): void
    {
        //Arrange
        $connectionData = new ConnectionData(
            'sandbox',
            'merchant1',
            'sequra',
            new AuthorizationCredentials('username', 'password'),
            '1'
        );
        $this->connectionService->saveConnectionData($connectionData);
        $signaturePayload = [
            StoreContext::getInstance()->getStoreId(),
            $this->integrationStoreIntegrationService->getWebhookUrl()->getPath(),
            $connectionData->getAuthorizationCredentials()->getUsername(),
            $connectionData->getMerchantId()
        ];
        $signature = HMAC::generateHMAC(
            $signaturePayload,
            $connectionData->getAuthorizationCredentials()->getPassword()
        );
        $this->shopCategoryService->setMockCategories(
            [
                new Category(1, 'Test1'),
                new Category(2, 'Test2'),
                new Category(3, 'Test3')
            ]
        );

        //Act
        $response = ConfigurationWebhookAPI::configurationHandler()->handleRequest(
            'merchant1',
            $signature,
            [
                'topic' => 'get-shop-categories',
                'page' => 1,
                'limit' => 5,
                'search' => '',
            ]
        );

        //Assert
        self::assertTrue($response->isSuccessful());
        self::assertEquals([
            [
                'id' => 1,
                'name' => 'Test1'
            ],
            [
                'id' => 2,
                'name' => 'Test2'
            ],
            [
                'id' => 3,
                'name' => 'Test3'
            ]
        ], $response->toArray());
    }

    /**
     * @return void
     *
     * @throws InvalidEnvironmentException
     * @throws EmptyCategoryParameterException
     */
    public function testGetSellingCountriesResponse(): void
    {
        //Arrange
        $connectionData = new ConnectionData(
            'sandbox',
            'merchant1',
            'sequra',
            new AuthorizationCredentials('username', 'password'),
            '1'
        );
        $this->connectionService->saveConnectionData($connectionData);
        $signaturePayload = [
            StoreContext::getInstance()->getStoreId(),
            $this->integrationStoreIntegrationService->getWebhookUrl()->getPath(),
            $connectionData->getAuthorizationCredentials()->getUsername(),
            $connectionData->getMerchantId()
        ];
        $signature = HMAC::generateHMAC(
            $signaturePayload,
            $connectionData->getAuthorizationCredentials()->getPassword()
        );
        $this->sellingCountriesService->setSellingCountries(
            [
                'ES',
                'FR',
                'IT',
                'PT'
            ]
        );

        //Act
        $response = ConfigurationWebhookAPI::configurationHandler()->handleRequest(
            'merchant1',
            $signature,
            [
                'topic' => 'get-selling-countries',
                'page' => 1,
                'limit' => 5,
                'search' => '',
            ]
        );

        //Assert
        self::assertTrue($response->isSuccessful());
        self::assertEquals([
            'ES',
            'FR',
            'IT',
            'PT'
        ], $response->toArray());
    }

    /**
     * @return void
     *
     * @throws InvalidEnvironmentException
     * @throws EmptyCategoryParameterException
     */
    public function testGetWidgetSettingsResponse(): void
    {
        //Arrange
        $connectionData = new ConnectionData(
            'sandbox',
            'merchant1',
            'sequra',
            new AuthorizationCredentials('username', 'password'),
            '1'
        );
        $this->connectionService->saveConnectionData($connectionData);
        $signaturePayload = [
            StoreContext::getInstance()->getStoreId(),
            $this->integrationStoreIntegrationService->getWebhookUrl()->getPath(),
            $connectionData->getAuthorizationCredentials()->getUsername(),
            $connectionData->getMerchantId()
        ];
        $signature = HMAC::generateHMAC(
            $signaturePayload,
            $connectionData->getAuthorizationCredentials()->getPassword()
        );

        $widgetSettings = new WidgetSettings(
            true,
            true,
            true,
            '{"alignment":"center","amount-font-bold":"true","amount-font-color":"#1C1C1C","amount-font-size":"15","background-color":"white","border-color":"#B1AEBA","border-radius":"","class":"","font-color":"#1C1C1C","link-font-color":"#1C1C1C","link-underline":"true","no-costs-claim":"","size":"M","starting-text":"only","type":"banner"}',
            // widgetConfig
            new WidgetSelectorSettings(
                '.wc-block-components-product-price>.amount,.wc-block-components-product-price ins .amount',
                '.summary>.price',
                '',
                '.woocommerce-variation-price .price>.amount,.woocommerce-variation-price .price ins .amount',
                '.variations',
                [
                    new CustomWidgetsSettings(
                        '#add-to-cart',
                        'i1',
                        true,
                        '{"alignment":"center","amount-font-bold":"true","amount-font-color":"#1C1C1C","amount-font-size":"15","background-color":"white","border-color":"#B1AEBA","border-radius":"","class":"","font-color":"#1C1C1C","link-font-color":"#1C1C1C","link-underline":"true","no-costs-claim":"","size":"M","starting-text":"only","type":"banner","branding":"black"}'
                    ),
                    new CustomWidgetsSettings(
                        '',
                        'sp1',
                        false,
                        ''
                    )
                ]
            ),
            new WidgetSelectorSettings(
                '.wp-block-woocommerce-cart-totals-block .wc-block-components-totals-footer-item .wc-block-components-totals-item__value',
                '.wp-block-woocommerce-cart-totals-block .wc-block-components-totals-footer-item',
                'pp3'
            ),
            new WidgetSelectorSettings(
                '.product .wc-block-components-product-price>.amount:first-child,.product .wc-block-components-product-price ins .amount',
                '.product .wc-block-components-product-price',
                'pp3'
            )
        );

        $this->widgetSettingsService->setWidgetSettings($widgetSettings);
        $this->paymentMethodsService->setMockPaymentMethods([
            new SeQuraPaymentMethod(
                'pp3',
                'Payez en plusieurs fois/Pagamento a rate/Pagamento Fracionado/Paga Fraccionado',
                'Description1',
                'part_payment',
                new SeQuraCost(0, 0, 0, 0),
                new \DateTime('0022-02-22T22:36:44Z'),
                new \DateTime('0022-02-22T22:36:44Z'),
                null,
                ''
            ),
            new SeQuraPaymentMethod(
                'sp1',
                'Divide tu pago en 3',
                'Description1',
                'part_payment',
                new SeQuraCost(0, 0, 0, 0),
                new \DateTime('0022-02-22T22:36:44Z'),
                new \DateTime('0022-02-22T22:36:44Z'),
                null,
                ''
            ),
            new SeQuraPaymentMethod(
                'i1',
                'Paga Después',
                'Description1',
                'pay_later',
                new SeQuraCost(0, 0, 0, 0),
                new \DateTime('0022-02-22T22:36:44Z'),
                new \DateTime('0022-02-22T22:36:44Z'),
                null,
                ''
            )
        ]);

        //Act
        $response = ConfigurationWebhookAPI::configurationHandler()->handleRequest(
            'merchant1',
            $signature,
            [
                'topic' => 'get-widget-settings'
            ]
        );

        //Assert
        self::assertTrue($response->isSuccessful());
        self::assertEquals([
            "paymentMethods" => [
                [
                    "category" => "part_payment",
                    "product" => "pp3",
                    "title" => "Payez en plusieurs fois/Pagamento a rate/Pagamento Fracionado/Paga Fraccionado"
                ],
                [
                    "category" => "part_payment",
                    "product" => "sp1",
                    "title" => "Divide tu pago en 3"
                ],
                [
                    "category" => "pay_later",
                    "product" => "i1",
                    "title" => "Paga Después"
                ]
            ],
            "paymentMethodCategoriesForProductPage" => [
                "part_payment",
                "pay_later"
            ],
            "paymentMethodCategoriesForCart" => [
                "part_payment",
                "pay_later"
            ],
            "paymentMethodCategoriesForListing" => [
                "part_payment"
            ],
            "displayWidgetOnProductPage" => true,
            "showInstallmentAmountInProductListing" => true,
            "showInstallmentAmountInCartPage" => true,
            "productPriceSelector" => ".wc-block-components-product-price>.amount,.wc-block-components-product-price ins .amount",
            "defaultProductLocationSelector" => ".summary>.price",
            "altProductPriceSelector" => ".woocommerce-variation-price .price>.amount,.woocommerce-variation-price .price ins .amount",
            "altProductPriceTriggerSelector" => ".variations",
            "customLocations" => [
                [
                    "product" => "i1",
                    "selForTarget" => "#add-to-cart",
                    "displayWidget" => true,
                    "widgetStyles" => "{\"alignment\":\"center\",\"amount-font-bold\":\"true\",\"amount-font-color\":\"#1C1C1C\",\"amount-font-size\":\"15\",\"background-color\":\"white\",\"border-color\":\"#B1AEBA\",\"border-radius\":\"\",\"class\":\"\",\"font-color\":\"#1C1C1C\",\"link-font-color\":\"#1C1C1C\",\"link-underline\":\"true\",\"no-costs-claim\":\"\",\"size\":\"M\",\"starting-text\":\"only\",\"type\":\"banner\",\"branding\":\"black\"}"
                ],
                [
                    "product" => "sp1",
                    "selForTarget" => "",
                    "displayWidget" => false,
                    "widgetStyles" => ""
                ]
            ],
            "cartPriceSelector" => ".wp-block-woocommerce-cart-totals-block .wc-block-components-totals-footer-item .wc-block-components-totals-item__value",
            "cartLocationSelector" => ".wp-block-woocommerce-cart-totals-block .wc-block-components-totals-footer-item",
            "widgetOnCartPage" => "pp3",
            "listingPriceSelector" => ".product .wc-block-components-product-price>.amount:first-child,.product .wc-block-components-product-price ins .amount",
            "listingLocationSelector" => ".product .wc-block-components-product-price",
            "widgetOnListingPage" => "pp3",
            "widgetStyles" => "{\"alignment\":\"center\",\"amount-font-bold\":\"true\",\"amount-font-color\":\"#1C1C1C\",\"amount-font-size\":\"15\",\"background-color\":\"white\",\"border-color\":\"#B1AEBA\",\"border-radius\":\"\",\"class\":\"\",\"font-color\":\"#1C1C1C\",\"link-font-color\":\"#1C1C1C\",\"link-underline\":\"true\",\"no-costs-claim\":\"\",\"size\":\"M\",\"starting-text\":\"only\",\"type\":\"banner\"}"
        ], $response->toArray());
    }

    /**
     * @return void
     *
     * @throws InvalidEnvironmentException
     */
    public function testSaveWidgetSettingsResponse(): void
    {
        //Arrange
        $connectionData = new ConnectionData(
            'sandbox',
            'merchant1',
            'sequra',
            new AuthorizationCredentials('username', 'password'),
            '1'
        );
        $this->connectionService->saveConnectionData($connectionData);
        $signaturePayload = [
            StoreContext::getInstance()->getStoreId(),
            $this->integrationStoreIntegrationService->getWebhookUrl()->getPath(),
            $connectionData->getAuthorizationCredentials()->getUsername(),
            $connectionData->getMerchantId()
        ];
        $signature = HMAC::generateHMAC(
            $signaturePayload,
            $connectionData->getAuthorizationCredentials()->getPassword()
        );

        $widgetSettings = new WidgetSettings(
            false,
            true,
            true,
            '{"alignment":"center","amount-font-bold":"true","amount-font-color":"#1C1C1C","amount-font-size":"15","background-color":"white","border-color":"#B1AEBA","border-radius":"","class":"","font-color":"#1C1C1C","link-font-color":"#1C1C1C","link-underline":"true","no-costs-claim":"","size":"M","starting-text":"only","type":"banner"}',
            // widgetConfig
            new WidgetSelectorSettings(
                '.wc-block-components-product-price>.amount,.wc-block-components-product-price ins .amount',
                '.summary>.price',
                '',
                '.woocommerce-variation-price .price>.amount,.woocommerce-variation-price .price ins .amount',
                '.variations',
                [
                    new CustomWidgetsSettings(
                        '#add-to-cart',
                        'i1',
                        true,
                        '{"alignment":"center","amount-font-bold":"true","amount-font-color":"#1C1C1C","amount-font-size":"15","background-color":"white","border-color":"#B1AEBA","border-radius":"","class":"","font-color":"#1C1C1C","link-font-color":"#1C1C1C","link-underline":"true","no-costs-claim":"","size":"M","starting-text":"only","type":"banner","branding":"black"}'
                    ),
                    new CustomWidgetsSettings(
                        '',
                        'sp1',
                        false,
                        ''
                    )
                ]
            ),
            new WidgetSelectorSettings(
                '.wp-block-woocommerce-cart-totals-block .wc-block-components-totals-footer-item .wc-block-components-totals-item__value',
                '.wp-block-woocommerce-cart-totals-block .wc-block-components-totals-footer-item',
                'pp3'
            ),
            new WidgetSelectorSettings(
                '.product .wc-block-components-product-price>.amount:first-child,.product .wc-block-components-product-price ins .amount',
                '.product .wc-block-components-product-price',
                'pp3'
            )
        );

        $this->widgetSettingsService->setWidgetSettings($widgetSettings);
        $this->paymentMethodsService->setMockPaymentMethods([
            new SeQuraPaymentMethod(
                'pp3',
                'Payez en plusieurs fois/Pagamento a rate/Pagamento Fracionado/Paga Fraccionado',
                'Description1',
                'part_payment',
                new SeQuraCost(0, 0, 0, 0),
                new \DateTime('0022-02-22T22:36:44Z'),
                new \DateTime('0022-02-22T22:36:44Z'),
                null,
                ''
            ),
            new SeQuraPaymentMethod(
                'sp1',
                'Divide tu pago en 3',
                'Description1',
                'part_payment',
                new SeQuraCost(0, 0, 0, 0),
                new \DateTime('0022-02-22T22:36:44Z'),
                new \DateTime('0022-02-22T22:36:44Z'),
                null,
                ''
            ),
            new SeQuraPaymentMethod(
                'i1',
                'Paga Después',
                'Description1',
                'pay_later',
                new SeQuraCost(0, 0, 0, 0),
                new \DateTime('0022-02-22T22:36:44Z'),
                new \DateTime('0022-02-22T22:36:44Z'),
                null,
                ''
            )
        ]);

        //Act
        $response = ConfigurationWebhookAPI::configurationHandler()->handleRequest(
            'merchant1',
            $signature,
            [
                "topic" => "save-widget-settings",
                "displayWidgetOnProductPage" => true,
                "widgetStyles" => "{\"alignment\":\"center\",\"amount-font-bold\":\"true\",\"amount-font-color\":\"#1C1C1C\",\"amount-font-size\":\"15\",\"background-color\":\"white\",\"border-color\":\"#B1AEBA\",\"border-radius\":\"\",\"class\":\"\",\"font-color\":\"#1C1C1C\",\"link-font-color\":\"#1C1C1C\",\"link-underline\":\"true\",\"no-costs-claim\":\"\",\"size\":\"M\",\"starting-text\":\"only\",\"type\":\"banner\"}",
                "showInstallmentAmountInProductListing" => true,
                "showInstallmentAmountInCartPage" => true,
                "productPriceSelector" => ".wc-block-components-product-price>.amount,.wc-block-components-product-price ins .amount",
                "altProductPriceSelector" => ".woocommerce-variation-price .price>.amount,.woocommerce-variation-price .price ins .amount",
                "altProductPriceTriggerSelector" => ".variations",
                "defaultProductLocationSelector" => ".summary>.price",
                "customLocations" => [
                    [
                        "selForTarget" => "#add-to-cart",
                        "product" => "i1",
                        "widgetStyles" => "{\"alignment\":\"center\",\"amount-font-bold\":\"true\",\"amount-font-color\":\"#1C1C1C\",\"amount-font-size\":\"15\",\"background-color\":\"white\",\"border-color\":\"#B1AEBA\",\"border-radius\":\"\",\"class\":\"\",\"font-color\":\"#1C1C1C\",\"link-font-color\":\"#1C1C1C\",\"link-underline\":\"true\",\"no-costs-claim\":\"\",\"size\":\"M\",\"starting-text\":\"only\",\"type\":\"banner\",\"branding\":\"black\"}",
                        "displayWidget" => true
                    ],
                    [
                        "selForTarget" => "",
                        "product" => "sp1",
                        "widgetStyles" => "",
                        "displayWidget" => false
                    ]
                ],
                "cartPriceSelector" => ".wp-block-woocommerce-cart-totals-block .wc-block-components-totals-footer-item .wc-block-components-totals-item__value",
                "cartLocationSelector" => ".wp-block-woocommerce-cart-totals-block .wc-block-components-totals-footer-item",
                "widgetOnCartPage" => "pp3",
                "listingPriceSelector" => ".product .wc-block-components-product-price>.amount:first-child,.product .wc-block-components-product-price ins .amount",
                "listingLocationSelector" => ".product .wc-block-components-product-price",
                "widgetOnListingPage" => "pp3"
            ]
        );

        //Assert
        self::assertTrue($response->isSuccessful());
        self::assertEquals([], $response->toArray());
        self::assertTrue($this->widgetSettingsService->getWidgetSettings()->isDisplayOnProductPage());
    }

    /**
     * @return void
     *
     * @throws InvalidEnvironmentException
     * @throws EmptyCategoryParameterException
     */
    public function testGetGeneralSettingsResponse(): void
    {
        //Arrange
        $connectionData = new ConnectionData(
            'sandbox',
            'merchant1',
            'sequra',
            new AuthorizationCredentials('username', 'password'),
            '1'
        );
        $this->connectionService->saveConnectionData($connectionData);
        $signaturePayload = [
            StoreContext::getInstance()->getStoreId(),
            $this->integrationStoreIntegrationService->getWebhookUrl()->getPath(),
            $connectionData->getAuthorizationCredentials()->getUsername(),
            $connectionData->getMerchantId()
        ];
        $signature = HMAC::generateHMAC(
            $signaturePayload,
            $connectionData->getAuthorizationCredentials()->getPassword()
        );
        $generalSettings = new GeneralSettings(
            false,
            true,
            ["127.0.0.1"],
            ["1", "11"],
            ["16"],
            ["ES"],
            ["ES", "FR", "IT"],
            ["ES"],
            "P1Y"
        );

        $this->generalSettingsService->saveGeneralSettings($generalSettings);
        $this->shopCategoryService->setMockCategories([new Category('16', 'Accessories')]);
        $this->productService->setMockShopProducts([
            new ShopProduct('1', 'sku1', 'Product 001'),
            new ShopProduct('11', 'sku2', 'Product 011')
        ]);

        //Act
        $response = ConfigurationWebhookAPI::configurationHandler()->handleRequest(
            'merchant1',
            $signature,
            [
                "topic" => "get-general-settings"
            ]
        );

        //Assert
        self::assertTrue($response->isSuccessful());
        self::assertEquals([
            'sendOrderReportsPeriodicallyToSeQura' => false,
            'showSeQuraCheckoutAsHostedPage' => true,
            'allowedIPAddresses' => [
                '127.0.0.1'
            ],
            'excludedProducts' => [
                ['id' => '1', 'name' => 'Product 001'],
                ['id' => '11', 'name' => 'Product 011']
            ],
            'excludedCategories' => [
                ['id' => '16', 'name' => 'Accessories']
            ],
            'enabledForServices' => [
                'ES'
            ],
            'allowFirstServicePaymentDelay' => [
                'ES',
                'FR',
                'IT'
            ],
            'allowServiceRegistrationItems' => [
                'ES'
            ],
            'defaultServicesEndDate' => 'P1Y'
        ], $response->toArray());
    }

    /**
     * @return void
     *
     * @throws InvalidEnvironmentException
     * @throws EmptyCategoryParameterException
     */
    public function testGetGeneralSettingsResponseNoGeneralSettings(): void
    {
        //Arrange
        $connectionData = new ConnectionData(
            'sandbox',
            'merchant1',
            'sequra',
            new AuthorizationCredentials('username', 'password'),
            '1'
        );
        $this->connectionService->saveConnectionData($connectionData);
        $signaturePayload = [
            StoreContext::getInstance()->getStoreId(),
            $this->integrationStoreIntegrationService->getWebhookUrl()->getPath(),
            $connectionData->getAuthorizationCredentials()->getUsername(),
            $connectionData->getMerchantId()
        ];
        $signature = HMAC::generateHMAC(
            $signaturePayload,
            $connectionData->getAuthorizationCredentials()->getPassword()
        );

        $this->shopCategoryService->setMockCategories([new Category('16', 'Accessories')]);
        $this->productService->setMockShopProducts([
            new ShopProduct('1', 'sku1', 'Product 001'),
            new ShopProduct('11', 'sku2', 'Product 011')
        ]);

        //Act
        $response = ConfigurationWebhookAPI::configurationHandler()->handleRequest(
            'merchant1',
            $signature,
            [
                "topic" => "get-general-settings"
            ]
        );

        //Assert
        self::assertTrue($response->isSuccessful());
        self::assertEmpty($response->toArray());
    }

    /**
     * @return void
     *
     * @throws InvalidEnvironmentException
     * @throws EmptyCategoryParameterException
     */
    public function testSaveGeneralSettingsResponse(): void
    {
        //Arrange
        $connectionData = new ConnectionData(
            'sandbox',
            'merchant1',
            'sequra',
            new AuthorizationCredentials('username', 'password'),
            '1'
        );
        $this->connectionService->saveConnectionData($connectionData);
        $signaturePayload = [
            StoreContext::getInstance()->getStoreId(),
            $this->integrationStoreIntegrationService->getWebhookUrl()->getPath(),
            $connectionData->getAuthorizationCredentials()->getUsername(),
            $connectionData->getMerchantId()
        ];
        $signature = HMAC::generateHMAC(
            $signaturePayload,
            $connectionData->getAuthorizationCredentials()->getPassword()
        );

        $this->shopCategoryService->setMockCategories([new Category('16', 'Accessories')]);
        $this->domainSellingCountriesService->setMockSellingCountries(
            [
                new SellingCountry("ES", 'Spain', 'merchantSpain'),
                new SellingCountry("FR", 'France', 'merchantFrance'),
                new SellingCountry("IT", 'Italy', 'merchantItaly'),
                new SellingCountry("PT", 'Portugal', 'merchantPortugal'),
                new SellingCountry("UK", 'United Kingdom', 'merchantUk')
            ]
        );

        //Act
        $response = ConfigurationWebhookAPI::configurationHandler()->handleRequest(
            'merchant1',
            $signature,
            [
                "topic" => "save-general-settings",
                "showSeQuraCheckoutAsHostedPage" => false,
                "sendOrderReportsPeriodicallyToSeQura" => false,
                "allowedIPAddresses" => [
                    "127.0.0.1"
                ],
                "excludedCategories" => [
                    "16"
                ],
                "excludedProducts" => [
                    "1",
                    "2"
                ],
                "sellingCountries" => [
                    "ES",
                    "FR",
                    "IT",
                    "PT"
                ],
                "enabledForServices" => [],
                "allowFirstServicePaymentDelay" => [],
                "allowServiceRegistrationItems" => [],
                "defaultServicesEndDate" => "P1Y"
            ]
        );

        //Assert
        $generalSettings = $this->generalSettingsService->getGeneralSettings();

        self::assertTrue($response->isSuccessful());
        self::assertEmpty($response->toArray());
        self::assertFalse($generalSettings->isShowSeQuraCheckoutAsHostedPage());
        self::assertEquals(['127.0.0.1'], $generalSettings->getAllowedIPAddresses());
    }

    /**
     * @return void
     *
     * @throws InvalidEnvironmentException
     * @throws EmptyCategoryParameterException
     */
    public function testGetOrderStatusListResponse(): void
    {
        //Arrange
        $connectionData = new ConnectionData(
            'sandbox',
            'merchant1',
            'sequra',
            new AuthorizationCredentials('username', 'password'),
            '1'
        );
        $this->connectionService->saveConnectionData($connectionData);
        $signaturePayload = [
            StoreContext::getInstance()->getStoreId(),
            $this->integrationStoreIntegrationService->getWebhookUrl()->getPath(),
            $connectionData->getAuthorizationCredentials()->getUsername(),
            $connectionData->getMerchantId()
        ];
        $signature = HMAC::generateHMAC(
            $signaturePayload,
            $connectionData->getAuthorizationCredentials()->getPassword()
        );

        $this->shopCategoryService->setMockCategories([new Category('16', 'Accessories')]);
        $this->shopOrderStatusService->setMockShopOrderStatuses(
            [
                new OrderStatus('wc-pending', 'Pending payment'),
                new OrderStatus('wc-processing', 'Processing'),
                new OrderStatus('wc-on-hold', 'On hold'),
                new OrderStatus('wc-completed', 'Completed'),
                new OrderStatus('wc-cancelled', 'Cancelled'),
                new OrderStatus('wc-refunded', 'Refunded'),
                new OrderStatus('wc-failed', 'Failed'),
                new OrderStatus('wc-checkout-draft', 'Draft'),
            ]
        );

        //Act
        $response = ConfigurationWebhookAPI::configurationHandler()->handleRequest(
            'merchant1',
            $signature,
            [
                "topic" => "get-order-status-list"
            ]
        );

        //Assert
        self::assertTrue($response->isSuccessful());
        self::assertEquals(
            [
                ['id' => 'wc-pending', 'name' => 'Pending payment'],
                ['id' => 'wc-processing', 'name' => 'Processing'],
                ['id' => 'wc-on-hold', 'name' => 'On hold'],
                ['id' => 'wc-completed', 'name' => 'Completed'],
                ['id' => 'wc-cancelled', 'name' => 'Cancelled'],
                ['id' => 'wc-refunded', 'name' => 'Refunded'],
                ['id' => 'wc-failed', 'name' => 'Failed'],
                ['id' => 'wc-checkout-draft', 'name' => 'Draft']
            ], $response->toArray());
    }

    /**
     * @return void
     *
     * @throws InvalidEnvironmentException
     * @throws EmptyCategoryParameterException
     */
    public function testGetOrderStatusSettingsResponse(): void
    {
        //Arrange
        $connectionData = new ConnectionData(
            'sandbox',
            'merchant1',
            'sequra',
            new AuthorizationCredentials('username', 'password'),
            '1'
        );
        $this->connectionService->saveConnectionData($connectionData);
        $signaturePayload = [
            StoreContext::getInstance()->getStoreId(),
            $this->integrationStoreIntegrationService->getWebhookUrl()->getPath(),
            $connectionData->getAuthorizationCredentials()->getUsername(),
            $connectionData->getMerchantId()
        ];
        $signature = HMAC::generateHMAC(
            $signaturePayload,
            $connectionData->getAuthorizationCredentials()->getPassword()
        );

        $this->shopCategoryService->setMockCategories([new Category('16', 'Accessories')]);
        $this->orderStatusSettingsService->setMockOrderStatusSettings(
            [
                new OrderStatusMapping('approved', 'wc-processing'),
                new OrderStatusMapping('needs_review', 'wc-pending'),
                new OrderStatusMapping('cancelled', 'wc-cancelled'),
                new OrderStatusMapping('solicited', 'wc-completed')
            ]
        );

        //Act
        $response = ConfigurationWebhookAPI::configurationHandler()->handleRequest(
            'merchant1',
            $signature,
            [
                "topic" => "get-order-status-settings"
            ]
        );

        //Assert
        self::assertTrue($response->isSuccessful());
        self::assertEquals(
            [
                ['sequraStatus' => 'approved', 'shopStatus' => 'wc-processing'],
                ['sequraStatus' => 'needs_review', 'shopStatus' => 'wc-pending'],
                ['sequraStatus' => 'cancelled', 'shopStatus' => 'wc-cancelled'],
                ['sequraStatus' => 'solicited', 'shopStatus' => 'wc-completed'],
            ], $response->toArray());
    }

    /**
     * @return void
     *
     * @throws InvalidEnvironmentException
     * @throws EmptyCategoryParameterException
     */
    public function testSaveOrderStatusSettingsResponse(): void
    {
        //Arrange
        $connectionData = new ConnectionData(
            'sandbox',
            'merchant1',
            'sequra',
            new AuthorizationCredentials('username', 'password'),
            '1'
        );
        $this->connectionService->saveConnectionData($connectionData);
        $signaturePayload = [
            StoreContext::getInstance()->getStoreId(),
            $this->integrationStoreIntegrationService->getWebhookUrl()->getPath(),
            $connectionData->getAuthorizationCredentials()->getUsername(),
            $connectionData->getMerchantId()
        ];
        $signature = HMAC::generateHMAC(
            $signaturePayload,
            $connectionData->getAuthorizationCredentials()->getPassword()
        );

        $this->shopCategoryService->setMockCategories([new Category('16', 'Accessories')]);
        $this->orderStatusSettingsService->setMockOrderStatusSettings(
            [
                new OrderStatusMapping('approved', 'wc-processing'),
                new OrderStatusMapping('needs_review', 'wc-pending'),
                new OrderStatusMapping('cancelled', 'wc-cancelled'),
                new OrderStatusMapping('solicited', 'wc-completed')
            ]
        );

        //Act
        $response = ConfigurationWebhookAPI::configurationHandler()->handleRequest(
            'merchant1',
            $signature,
            [
                "topic" => "save-order-status-settings",
                "orderStatusMappings" => [
                    ['sequraStatus' => 'approved', 'shopStatus' => 'wc-processing'],
                    ['sequraStatus' => 'needs_review', 'shopStatus' => 'wc-pending'],
                    ['sequraStatus' => 'cancelled', 'shopStatus' => 'wc-cancelled'],
                    ['sequraStatus' => 'solicited', 'shopStatus' => 'wc-completed'],
                ]
            ]
        );

        //Assert
        self::assertTrue($response->isSuccessful());
        self::assertEmpty($response->toArray());
        self::assertCount(4, $this->orderStatusSettingsService->getOrderStatusSettings());
    }

    /**
     * @return void
     *
     * @throws InvalidEnvironmentException
     * @throws EmptyCategoryParameterException
     */
    public function testSetAdvancedSettingsResponse(): void
    {
        //Arrange
        $connectionData = new ConnectionData(
            'sandbox',
            'merchant1',
            'sequra',
            new AuthorizationCredentials('username', 'password'),
            '1'
        );
        $this->connectionService->saveConnectionData($connectionData);
        $signaturePayload = [
            StoreContext::getInstance()->getStoreId(),
            $this->integrationStoreIntegrationService->getWebhookUrl()->getPath(),
            $connectionData->getAuthorizationCredentials()->getUsername(),
            $connectionData->getMerchantId()
        ];
        $signature = HMAC::generateHMAC(
            $signaturePayload,
            $connectionData->getAuthorizationCredentials()->getPassword()
        );

        $advancedSettings = new AdvancedSettings(true, 1);
        $this->advancedSettingsService->setAdvancedSettings($advancedSettings);

        //Act
        $response = ConfigurationWebhookAPI::configurationHandler()->handleRequest(
            'merchant1',
            $signature,
            [
                "topic" => "save-advanced-settings",
                "isEnabled" => false,
                "level" => 3
            ]
        );

        //Assert
        self::assertTrue($response->isSuccessful());
        self::assertEmpty($response->toArray());
    }

    /**
     * @return void
     *
     * @throws InvalidEnvironmentException
     * @throws EmptyCategoryParameterException
     */
    public function testGetAdvancedSettingsResponse(): void
    {
        //Arrange
        $connectionData = new ConnectionData(
            'sandbox',
            'merchant1',
            'sequra',
            new AuthorizationCredentials('username', 'password'),
            '1'
        );
        $this->connectionService->saveConnectionData($connectionData);
        $signaturePayload = [
            StoreContext::getInstance()->getStoreId(),
            $this->integrationStoreIntegrationService->getWebhookUrl()->getPath(),
            $connectionData->getAuthorizationCredentials()->getUsername(),
            $connectionData->getMerchantId()
        ];
        $signature = HMAC::generateHMAC(
            $signaturePayload,
            $connectionData->getAuthorizationCredentials()->getPassword()
        );

        $advancedSettings = new AdvancedSettings(true, 1);
        $this->advancedSettingsService->setAdvancedSettings($advancedSettings);

        //Act
        $response = ConfigurationWebhookAPI::configurationHandler()->handleRequest(
            'merchant1',
            $signature,
            [
                "topic" => "get-advanced-settings"
            ]
        );

        //Assert
        self::assertTrue($response->isSuccessful());
        self::assertEquals([
            'isEnabled' => true,
            'level' => 1
        ],$response->toArray());
    }
}
