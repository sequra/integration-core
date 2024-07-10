<?php

namespace SeQura\Core\Tests\BusinessLogic\Common;

use PHPUnit\Framework\TestCase;
use SeQura\Core\BusinessLogic\AdminAPI\Connection\ConnectionController;
use SeQura\Core\BusinessLogic\AdminAPI\CountryConfiguration\CountryConfigurationController;
use SeQura\Core\BusinessLogic\AdminAPI\GeneralSettings\GeneralSettingsController;
use SeQura\Core\BusinessLogic\AdminAPI\Integration\IntegrationController;
use SeQura\Core\BusinessLogic\AdminAPI\OrderStatusSettings\OrderStatusSettingsController;
use SeQura\Core\BusinessLogic\AdminAPI\PaymentMethods\PaymentMethodsController;
use SeQura\Core\BusinessLogic\AdminAPI\PromotionalWidgets\PromotionalWidgetsController;
use SeQura\Core\BusinessLogic\AdminAPI\Store\StoreController;
use SeQura\Core\BusinessLogic\AdminAPI\TransactionLogs\TransactionLogsController;
use SeQura\Core\BusinessLogic\DataAccess\ConnectionData\Entities\ConnectionData;
use SeQura\Core\BusinessLogic\DataAccess\ConnectionData\Repositories\ConnectionDataRepository;
use SeQura\Core\BusinessLogic\DataAccess\CountryConfiguration\Entities\CountryConfiguration;
use SeQura\Core\BusinessLogic\DataAccess\CountryConfiguration\Repositories\CountryConfigurationRepository;
use SeQura\Core\BusinessLogic\DataAccess\GeneralSettings\Entities\GeneralSettings;
use SeQura\Core\BusinessLogic\DataAccess\GeneralSettings\Repositories\GeneralSettingsRepository;
use SeQura\Core\BusinessLogic\DataAccess\Order\Repositories\SeQuraOrderRepository;
use SeQura\Core\BusinessLogic\DataAccess\OrderSettings\Entities\OrderStatusSettings;
use SeQura\Core\BusinessLogic\DataAccess\OrderSettings\Repositories\OrderStatusMappingRepository;
use SeQura\Core\BusinessLogic\DataAccess\PromotionalWidgets\Entities\WidgetSettings;
use SeQura\Core\BusinessLogic\DataAccess\PromotionalWidgets\Repositories\WidgetSettingsRepository;
use SeQura\Core\BusinessLogic\DataAccess\SendReport\Entities\SendReport;
use SeQura\Core\BusinessLogic\DataAccess\SendReport\Repositories\SendReportRepository;
use SeQura\Core\BusinessLogic\DataAccess\StatisticalData\Entities\StatisticalData;
use SeQura\Core\BusinessLogic\DataAccess\StatisticalData\Repositories\StatisticalDataRepository;
use SeQura\Core\BusinessLogic\DataAccess\TransactionLog\Entities\TransactionLog;
use SeQura\Core\BusinessLogic\DataAccess\TransactionLog\Repositories\TransactionLogRepository;
use SeQura\Core\BusinessLogic\Domain\Connection\ProxyContracts\ConnectionProxyInterface;
use SeQura\Core\BusinessLogic\Domain\Connection\RepositoryContracts\ConnectionDataRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\RepositoryContracts\CountryConfigurationRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Services\CountryConfigurationService;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Services\SellingCountriesService;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\RepositoryContracts\GeneralSettingsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Services\CategoryService;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Services\GeneralSettingsService;
use SeQura\Core\BusinessLogic\Domain\Integration\Category\CategoryServiceInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\OrderReport\OrderReportServiceInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\SellingCountries\SellingCountriesServiceInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\ShopOrderStatuses\ShopOrderStatusesServiceInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\Store\StoreServiceInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\Version\VersionServiceInterface;
use SeQura\Core\BusinessLogic\Domain\Merchant\ProxyContracts\MerchantProxyInterface;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Item\AbstractItemFactory;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Item\ItemFactory;
use SeQura\Core\BusinessLogic\Domain\Order\Models\SeQuraOrder;
use SeQura\Core\BusinessLogic\Domain\Order\ProxyContracts\OrderProxyInterface;
use SeQura\Core\BusinessLogic\Domain\Order\RepositoryContracts\SeQuraOrderRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Order\Service\OrderService;
use SeQura\Core\BusinessLogic\Domain\OrderReport\ProxyContracts\OrderReportProxyInterface;
use SeQura\Core\BusinessLogic\Domain\OrderReport\Service\OrderReportService;
use SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\RepositoryContracts\OrderStatusSettingsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\Services\OrderStatusSettingsService;
use SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\Services\ShopOrderStatusesService;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Services\PaymentMethodsService;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\ProxyContracts\WidgetsProxyInterface;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\RepositoryContracts\WidgetSettingsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Services\WidgetSettingsService;
use SeQura\Core\BusinessLogic\Domain\SendReport\RepositoryContracts\SendReportRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\StatisticalData\RepositoryContracts\StatisticalDataRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\StatisticalData\Services\StatisticalDataService;
use SeQura\Core\BusinessLogic\Domain\Stores\Services\StoreService;
use SeQura\Core\BusinessLogic\Domain\UIState\Services\UIStateService;
use SeQura\Core\BusinessLogic\Domain\Version\Services\VersionService;
use SeQura\Core\BusinessLogic\Providers\QueueNameProvider\Contract\QueueNameProviderInterface;
use SeQura\Core\BusinessLogic\Providers\QueueNameProvider\QueueNameProvider;
use SeQura\Core\BusinessLogic\SeQuraAPI\Connection\ConnectionProxy;
use SeQura\Core\BusinessLogic\SeQuraAPI\Merchant\MerchantProxy;
use SeQura\Core\BusinessLogic\SeQuraAPI\Order\OrderProxy;
use SeQura\Core\BusinessLogic\SeQuraAPI\OrderReport\OrderReportProxy;
use SeQura\Core\BusinessLogic\SeQuraAPI\Widgets\WidgetsProxy;
use SeQura\Core\BusinessLogic\TransactionLog\RepositoryContracts\TransactionLogRepositoryInterface;
use SeQura\Core\BusinessLogic\TransactionLog\Services\TransactionLogService;
use SeQura\Core\BusinessLogic\Utility\EncryptorInterface;
use SeQura\Core\BusinessLogic\Webhook\Handler\WebhookHandler;
use SeQura\Core\BusinessLogic\Webhook\Services\ShopOrderService;
use SeQura\Core\BusinessLogic\Webhook\Validator\WebhookValidator;
use SeQura\Core\BusinessLogic\WebhookAPI\Controller\WebhookController;
use SeQura\Core\Infrastructure\Configuration\ConfigEntity;
use SeQura\Core\Infrastructure\Configuration\Configuration;
use SeQura\Core\Infrastructure\Configuration\ConfigurationManager;
use SeQura\Core\Infrastructure\Http\HttpClient;
use SeQura\Core\Infrastructure\Logger\Interfaces\ShopLoggerAdapter;
use SeQura\Core\Infrastructure\Logger\Logger;
use SeQura\Core\Infrastructure\Logger\LoggerConfiguration;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use SeQura\Core\Infrastructure\Serializer\Concrete\JsonSerializer;
use SeQura\Core\Infrastructure\Serializer\Serializer;
use SeQura\Core\Infrastructure\TaskExecution\Events\QueueItemStateTransitionEventBus;
use SeQura\Core\Infrastructure\TaskExecution\Interfaces\TaskRunnerWakeup;
use SeQura\Core\Infrastructure\TaskExecution\QueueItem;
use SeQura\Core\Infrastructure\TaskExecution\QueueService;
use SeQura\Core\Infrastructure\Utility\Events\EventBus;
use SeQura\Core\Infrastructure\Utility\TimeProvider;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MemoryRepositoryWithConditionalDelete;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\TestEncryptor;
use SeQura\Core\Tests\BusinessLogic\WebhookAPI\MockComponents\MockShopOrderService;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\Logger\TestShopLogger;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\ORM\MemoryQueueItemRepository;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\ORM\MemoryRepository;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\ORM\MemoryStorage;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\ORM\TestRepositoryRegistry;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution\TestQueueService;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution\TestTaskRunnerWakeupService;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TestConfigurationManager;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TestHttpClient;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\Utility\Events\TestEventEmitter;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\Utility\TestTimeProvider;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

class BaseTestCase extends TestCase
{
    /**
     * @return void
     * @throws RepositoryClassException
     */
    protected function setUp(): void
    {
        parent::setUp();

        TestServiceRegister::getInstance();
        new TestServiceRegister([
            Configuration::class => function () {
                return MockComponents\Configuration::getInstance();
            },
            Serializer::class => function () {
                return new JsonSerializer();
            },
            QueueService::class => function () {
                return new TestQueueService();
            },
            EventBus::class => function () {
                return TestEventEmitter::getInstance();
            },
            TaskRunnerWakeup::class => function () {
                return new TestTaskRunnerWakeupService();
            },
            QueueItemStateTransitionEventBus::CLASS_NAME => function () {
                return QueueItemStateTransitionEventBus::getInstance();
            },
            ShopLoggerAdapter::class => function () {
                return new TestShopLogger();
            },
            HttpClient::class => function () {
                return new TestHttpClient();
            },
            StoreContext::class => function () {
                return StoreContext::getInstance();
            },
            EncryptorInterface::class => function () {
                return new TestEncryptor();
            },
            OrderStatusSettingsRepositoryInterface::class => function () {
                return new OrderStatusMappingRepository(
                    TestRepositoryRegistry::getRepository(OrderStatusSettings::getClassName()),
                    StoreContext::getInstance()
                );
            },
            StatisticalDataRepositoryInterface::class => function () {
                return new StatisticalDataRepository(
                    TestRepositoryRegistry::getRepository(StatisticalData::getClassName()),
                    StoreContext::getInstance()
                );
            },
            CountryConfigurationRepositoryInterface::class => function () {
                return new CountryConfigurationRepository(
                    TestRepositoryRegistry::getRepository(CountryConfiguration::getClassName()),
                    StoreContext::getInstance()
                );
            },
            GeneralSettingsRepositoryInterface::class => function () {
                return new GeneralSettingsRepository(
                    TestRepositoryRegistry::getRepository(GeneralSettings::getClassName()),
                    StoreContext::getInstance()
                );
            },
            ConnectionDataRepositoryInterface::class => function () {
                return new ConnectionDataRepository(
                    TestRepositoryRegistry::getRepository(ConnectionData::getClassName()),
                    StoreContext::getInstance()
                );
            },
            TransactionLogRepositoryInterface::class => function () {
                return new TransactionLogRepository(
                    TestRepositoryRegistry::getRepository(TransactionLog::getClassName()),
                    StoreContext::getInstance()
                );
            },
            OrderService::class => static function () {
                return new OrderService(
                    TestServiceRegister::getService(OrderProxyInterface::class),
                    TestServiceRegister::getService(SeQuraOrderRepositoryInterface::class)
                );
            },
            OrderReportService::class => static function () {
                return new OrderReportService(
                    TestServiceRegister::getService(OrderReportProxyInterface::class),
                    TestServiceRegister::getService(OrderReportServiceInterface::class),
                    TestServiceRegister::getService(SendReportRepositoryInterface::class)
                );
            },
            OrderStatusSettingsService::class => static function () {
                return new OrderStatusSettingsService(
                    TestServiceRegister::getService(OrderStatusSettingsRepositoryInterface::class),
                    TestServiceRegister::getService(ShopOrderStatusesServiceInterface::class)
                );
            },
            ConnectionService::class => static function () {
                return new ConnectionService(
                    TestServiceRegister::getService(ConnectionProxyInterface::class)
                );
            },
            PaymentMethodsService::class => static function () {
                return new PaymentMethodsService(
                    TestServiceRegister::getService(MerchantProxyInterface::class)
                );
            },
            StatisticalDataService::class => static function () {
                return new StatisticalDataService(
                    TestServiceRegister::getService(StatisticalDataRepositoryInterface::class),
                    TestServiceRegister::getService(SendReportRepositoryInterface::class),
                    TestServiceRegister::getService(TimeProvider::class)
                );
            },
            CountryConfigurationService::class => static function () {
                return new CountryConfigurationService(
                    TestServiceRegister::getService(CountryConfigurationRepositoryInterface::class),
                    TestServiceRegister::getService(SellingCountriesServiceInterface::class)
                );
            },
            GeneralSettingsService::class => static function () {
                return new GeneralSettingsService(
                    TestServiceRegister::getService(GeneralSettingsRepositoryInterface::class)
                );
            },
            TransactionLogService::class => static function () {
                return new TransactionLogService(
                    TestServiceRegister::getService(TransactionLogRepositoryInterface::class),
                    TestServiceRegister::getService(OrderService::class),
                    TestServiceRegister::getService(ShopOrderService::class)
                );
            },
            SellingCountriesService::class => static function () {
                return new SellingCountriesService(
                    TestServiceRegister::getService(SellingCountriesServiceInterface::class)
                );
            },
            ShopOrderStatusesService::class => static function () {
                return new ShopOrderStatusesService(
                    TestServiceRegister::getService(ShopOrderStatusesServiceInterface::class)
                );
            },
            VersionService::class => static function () {
                return new VersionService(
                    TestServiceRegister::getService(VersionServiceInterface::class)
                );
            },
            CategoryService::class => static function () {
                return new CategoryService(
                    TestServiceRegister::getService(CategoryServiceInterface::class)
                );
            },
            StoreService::class => static function () {
                return new StoreService(
                    TestServiceRegister::getService(StoreServiceInterface::class),
                    TestServiceRegister::getService(ConnectionDataRepositoryInterface::class)
                );
            },
            UIStateService::class => static function () {
                return new UIStateService(
                    TestServiceRegister::getService(ConnectionService::class),
                    TestServiceRegister::getService(ConnectionDataRepositoryInterface::class),
                    TestServiceRegister::getService(CountryConfigurationRepositoryInterface::class),
                    TestServiceRegister::getService(WidgetSettingsRepositoryInterface::class)
                );
            },
            ShopOrderService::class => function () {
                return new MockShopOrderService();
            },
            ConnectionController::class => function () {
                return new ConnectionController(
                    TestServiceRegister::getService(ConnectionService::class),
                    TestServiceRegister::getService(StatisticalDataService::class)
                );
            },
            CountryConfigurationController::class => function () {
                return new CountryConfigurationController(
                    TestServiceRegister::getService(CountryConfigurationService::class),
                    TestServiceRegister::getService(SellingCountriesService::class)
                );
            },
            OrderStatusSettingsController::class => function () {
                return new OrderStatusSettingsController(
                    TestServiceRegister::getService(OrderStatusSettingsService::class),
                    TestServiceRegister::getService(ShopOrderStatusesService::class)
                );
            },
            GeneralSettingsController::class => function () {
                return new GeneralSettingsController(
                    TestServiceRegister::getService(GeneralSettingsService::class),
                    TestServiceRegister::getService(CategoryService::class)
                );
            },
            TransactionLogsController::class => function () {
                return new TransactionLogsController(
                    TestServiceRegister::getService(TransactionLogService::class)
                );
            },
            PaymentMethodsController::class => function () {
                return new PaymentMethodsController(
                    TestServiceRegister::getService(PaymentMethodsService::class)
                );
            },
            IntegrationController::class => function () {
                return new IntegrationController(
                    TestServiceRegister::getService(VersionService::class),
                    TestServiceRegister::getService(Configuration::class),
                    TestServiceRegister::getService(UIStateService::class)
                );
            },
            StoreController::class => function () {
                return new StoreController(
                    TestServiceRegister::getService(StoreService::class)
                );
            },
            WebhookController::class => function () {
                return new WebhookController(
                    TestServiceRegister::getService(WebhookValidator::class),
                    TestServiceRegister::getService(WebhookHandler::class)
                );
            },
            WidgetSettingsRepositoryInterface::class => function () {
                return new WidgetSettingsRepository(
                    TestRepositoryRegistry::getRepository(WidgetSettings::getClassName()),
                    TestServiceRegister::getService(StoreContext::class)
                );
            },
            WidgetSettingsService::class => function () {
                return new WidgetSettingsService(
                    TestServiceRegister::getService(WidgetSettingsRepositoryInterface::class),
                    TestServiceRegister::getService(PaymentMethodsService::class),
                    TestServiceRegister::getService(CountryConfigurationService::class),
                    TestServiceRegister::getService(ConnectionService::class),
                    TestServiceRegister::getService(WidgetsProxyInterface::class)
                );
            },
            PromotionalWidgetsController::class => function () {
                return new PromotionalWidgetsController(
                    TestServiceRegister::getService(WidgetSettingsService::class)
                );
            },
            AbstractItemFactory::class => function () {
                return new ItemFactory();
            }
        ]);

        TestServiceRegister::registerService(
            QueueNameProviderInterface::class,
            static function () {
                return new QueueNameProvider();
            }
        );

        TestServiceRegister::registerService(
            TimeProvider::class,
            static function () {
                return TestTimeProvider::getInstance();
            }
        );

        TestServiceRegister::registerService(
            WebhookValidator::class,
            static function () {
                return new WebhookValidator();
            }
        );

        TestServiceRegister::registerService(
            ConfigurationManager::class,
            static function () {
                return new TestConfigurationManager();
            }
        );

        TestServiceRegister::registerService(
            WebhookHandler::class,
            static function () {
                return new WebhookHandler();
            }
        );

        TestServiceRegister::registerService(
            ConnectionProxyInterface::class,
            static function () {
                return new ConnectionProxy(
                    TestServiceRegister::getService(HttpClient::class)
                );
            }
        );

        TestServiceRegister::registerService(
            MerchantProxyInterface::class,
            static function () {
                return new MerchantProxy(
                    TestServiceRegister::getService(HttpClient::class),
                    TestServiceRegister::getService(ConnectionDataRepositoryInterface::class)
                );
            }
        );

        TestServiceRegister::registerService(
            OrderProxyInterface::class,
            static function () {
                return new OrderProxy(
                    TestServiceRegister::getService(HttpClient::class),
                    TestServiceRegister::getService(ConnectionDataRepositoryInterface::class)
                );
            }
        );

        TestServiceRegister::registerService(
            OrderReportProxyInterface::class,
            static function () {
                return new OrderReportProxy(
                    TestServiceRegister::getService(HttpClient::class),
                    TestServiceRegister::getService(ConnectionDataRepositoryInterface::class)
                );
            }
        );

        TestServiceRegister::registerService(
            WidgetsProxyInterface::class,
            static function () {
                return new WidgetsProxy(
                    TestServiceRegister::getService(HttpClient::class)
                );
            }
        );

        TestServiceRegister::registerService(
            ConnectionDataRepositoryInterface::class,
            static function () {
                return new ConnectionDataRepository(
                    TestRepositoryRegistry::getRepository(ConnectionData::getClassName()),
                    TestServiceRegister::getService(StoreContext::class)
                );
            }
        );

        TestServiceRegister::registerService(
            SeQuraOrderRepositoryInterface::class,
            static function () {
                return new SeQuraOrderRepository(
                    TestRepositoryRegistry::getRepository(SeQuraOrder::getClassName())
                );
            }
        );

        TestServiceRegister::registerService(
            SendReportRepositoryInterface::class,
            static function () {
                return new SendReportRepository(
                    TestRepositoryRegistry::getRepository(SendReport::getClassName()),
                    TestServiceRegister::getService(StoreContext::class)
                );
            }
        );

        TestRepositoryRegistry::registerRepository(ConfigEntity::getClassName(), MemoryRepository::getClassName());
        TestRepositoryRegistry::registerRepository(
            QueueItem::getClassName(),
            MemoryQueueItemRepository::getClassName()
        );
        TestRepositoryRegistry::registerRepository(SeQuraOrder::getClassName(), MemoryRepository::getClassName());
        TestRepositoryRegistry::registerRepository(
            OrderStatusSettings::getClassName(),
            MemoryRepository::getClassName()
        );
        TestRepositoryRegistry::registerRepository(ConnectionData::getClassName(), MemoryRepository::getClassName());
        TestRepositoryRegistry::registerRepository(StatisticalData::getClassName(), MemoryRepository::getClassName());
        TestRepositoryRegistry::registerRepository(GeneralSettings::getClassName(), MemoryRepository::getClassName());
        TestRepositoryRegistry::registerRepository(SendReport::getClassName(), MemoryRepository::getClassName());
        TestRepositoryRegistry::registerRepository(
            CountryConfiguration::getClassName(),
            MemoryRepository::getClassName()
        );
        TestRepositoryRegistry::registerRepository(WidgetSettings::getClassName(), MemoryRepository::getClassName());
        TestRepositoryRegistry::registerRepository(
            TransactionLog::getClassName(),
            MemoryRepositoryWithConditionalDelete::getClassName()
        );
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        TestRepositoryRegistry::cleanUp();
        MemoryStorage::reset();
        Logger::resetInstance();
        LoggerConfiguration::resetInstance();
    }
}
