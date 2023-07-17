<?php

namespace SeQura\Core\Tests\BusinessLogic\Common;

use PHPUnit\Framework\TestCase;
use SeQura\Core\BusinessLogic\AdminAPI\Connection\ConnectionController;
use SeQura\Core\BusinessLogic\AdminAPI\CountryConfiguration\CountryConfigurationController;
use SeQura\Core\BusinessLogic\AdminAPI\Integration\IntegrationController;
use SeQura\Core\BusinessLogic\AdminAPI\Store\StoreController;
use SeQura\Core\BusinessLogic\DataAccess\ConnectionData\Entities\ConnectionData;
use SeQura\Core\BusinessLogic\DataAccess\ConnectionData\Repositories\ConnectionDataRepository;
use SeQura\Core\BusinessLogic\DataAccess\CountryConfiguration\Entities\CountryConfiguration;
use SeQura\Core\BusinessLogic\DataAccess\CountryConfiguration\Repositories\CountryConfigurationRepository;
use SeQura\Core\BusinessLogic\DataAccess\Order\Repositories\SeQuraOrderRepository;
use SeQura\Core\BusinessLogic\DataAccess\OrderSettings\Entities\OrderStatusMapping;
use SeQura\Core\BusinessLogic\DataAccess\OrderSettings\Repositories\OrderStatusMappingRepository;
use SeQura\Core\BusinessLogic\DataAccess\StatisticalData\Entities\StatisticalData;
use SeQura\Core\BusinessLogic\DataAccess\StatisticalData\Repositories\StatisticalDataRepository;
use SeQura\Core\BusinessLogic\Domain\Connection\ProxyContracts\ConnectionProxyInterface;
use SeQura\Core\BusinessLogic\Domain\Connection\RepositoryContracts\ConnectionDataRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\RepositoryContracts\CountryConfigurationRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Services\CountryConfigurationService;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Services\SellingCountriesService;
use SeQura\Core\BusinessLogic\Domain\Integration\SellingCountries\SellingCountriesServiceInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\Store\StoreServiceInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\Version\VersionServiceInterface;
use SeQura\Core\BusinessLogic\Domain\Merchant\ProxyContracts\MerchantProxyInterface;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\Order\Models\SeQuraOrder;
use SeQura\Core\BusinessLogic\Domain\Order\ProxyContracts\OrderProxyInterface;
use SeQura\Core\BusinessLogic\Domain\Order\RepositoryContracts\SeQuraOrderRepositoryInterface;
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
use SeQura\Core\BusinessLogic\Utility\EncryptorInterface;
use SeQura\Core\BusinessLogic\Webhook\Handler\WebhookHandler;
use SeQura\Core\BusinessLogic\Webhook\Repositories\OrderStatusMappingRepository as OrderStatusMappingRepositoryInterface;
use SeQura\Core\BusinessLogic\Webhook\Services\ShopOrderService;
use SeQura\Core\BusinessLogic\Webhook\Services\StatusMappingService;
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
            OrderStatusMappingRepositoryInterface::class => function () {
                return new OrderStatusMappingRepository(
                    TestRepositoryRegistry::getRepository(OrderStatusMapping::getClassName()),
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
            ConnectionDataRepositoryInterface::class => function () {
                return new ConnectionDataRepository(
                    TestRepositoryRegistry::getRepository(ConnectionData::getClassName()),
                    StoreContext::getInstance()
                );
            },
            StatusMappingService::class => static function () {
                return new StatusMappingService(
                    TestServiceRegister::getService(OrderStatusMappingRepositoryInterface::class)
                );
            },
            ConnectionService::class => static function () {
                return new ConnectionService(
                    TestServiceRegister::getService(ConnectionProxyInterface::class)
                );
            },
            StatisticalDataService::class => static function () {
                return new StatisticalDataService(
                    TestServiceRegister::getService(StatisticalDataRepositoryInterface::class)
                );
            },
            CountryConfigurationService::class => static function () {
                return new CountryConfigurationService(
                    TestServiceRegister::getService(CountryConfigurationRepositoryInterface::class)
                );
            },
            SellingCountriesService::class => static function () {
                return new SellingCountriesService(
                    TestServiceRegister::getService(SellingCountriesServiceInterface::class)
                );
            },
            VersionService::class => static function () {
                return new VersionService(
                    TestServiceRegister::getService(VersionServiceInterface::class)
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
                    TestServiceRegister::getService(ConnectionDataRepositoryInterface::class),
                    TestServiceRegister::getService(CountryConfigurationRepositoryInterface::class)
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
                return new WebhookHandler(
                  TestServiceRegister::getService(QueueService::class),
                  TestServiceRegister::getService(QueueNameProviderInterface::class)
                );
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

        TestRepositoryRegistry::registerRepository(ConfigEntity::getClassName(), MemoryRepository::getClassName());
        TestRepositoryRegistry::registerRepository(
            QueueItem::getClassName(),
            MemoryQueueItemRepository::getClassName()
        );
        TestRepositoryRegistry::registerRepository(SeQuraOrder::getClassName(), MemoryRepository::getClassName());
        TestRepositoryRegistry::registerRepository(OrderStatusMapping::getClassName(), MemoryRepository::getClassName());
        TestRepositoryRegistry::registerRepository(ConnectionData::getClassName(), MemoryRepository::getClassName());
        TestRepositoryRegistry::registerRepository(StatisticalData::getClassName(), MemoryRepository::getClassName());
        TestRepositoryRegistry::registerRepository(
            CountryConfiguration::getClassName(),
            MemoryRepository::getClassName()
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
