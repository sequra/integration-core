<?php

namespace SeQura\Core\BusinessLogic;

use SeQura\Core\BusinessLogic\AdminAPI\Connection\ConnectionController;
use SeQura\Core\BusinessLogic\AdminAPI\CountryConfiguration\CountryConfigurationController;
use SeQura\Core\BusinessLogic\AdminAPI\Deployments\DeploymentsController;
use SeQura\Core\BusinessLogic\AdminAPI\Disconnect\DisconnectController;
use SeQura\Core\BusinessLogic\AdminAPI\GeneralSettings\GeneralSettingsController;
use SeQura\Core\BusinessLogic\AdminAPI\Integration\IntegrationController;
use SeQura\Core\BusinessLogic\AdminAPI\OrderStatusSettings\OrderStatusSettingsController;
use SeQura\Core\BusinessLogic\AdminAPI\PaymentMethods\PaymentMethodsController;
use SeQura\Core\BusinessLogic\AdminAPI\PromotionalWidgets\PromotionalWidgetsController;
use SeQura\Core\BusinessLogic\AdminAPI\Store\StoreController;
use SeQura\Core\BusinessLogic\AdminAPI\TransactionLogs\TransactionLogsController;
use SeQura\Core\BusinessLogic\CheckoutAPI\PaymentMethods\CachedPaymentMethodsController;
use SeQura\Core\BusinessLogic\CheckoutAPI\PromotionalWidgets\PromotionalWidgetsCheckoutController;
use SeQura\Core\BusinessLogic\CheckoutAPI\Solicitation\Controller\SolicitationController;
use SeQura\Core\BusinessLogic\DataAccess\ConnectionData\Entities\ConnectionData;
use SeQura\Core\BusinessLogic\DataAccess\ConnectionData\Repositories\ConnectionDataRepository;
use SeQura\Core\BusinessLogic\DataAccess\CountryConfiguration\Entities\CountryConfiguration;
use SeQura\Core\BusinessLogic\DataAccess\CountryConfiguration\Repositories\CountryConfigurationRepository;
use SeQura\Core\BusinessLogic\DataAccess\Credentials\Entities\Credentials;
use SeQura\Core\BusinessLogic\DataAccess\Credentials\Repositories\CredentialsRepository;
use SeQura\Core\BusinessLogic\DataAccess\Deployments\Entities\Deployment;
use SeQura\Core\BusinessLogic\DataAccess\Deployments\Repositories\DeploymentRepository;
use SeQura\Core\BusinessLogic\DataAccess\GeneralSettings\Entities\GeneralSettings;
use SeQura\Core\BusinessLogic\DataAccess\GeneralSettings\Repositories\GeneralSettingsRepository;
use SeQura\Core\BusinessLogic\DataAccess\Order\Repositories\SeQuraOrderRepository;
use SeQura\Core\BusinessLogic\DataAccess\OrderSettings\Entities\OrderStatusSettings;
use SeQura\Core\BusinessLogic\DataAccess\OrderSettings\Repositories\OrderStatusMappingRepository;
use SeQura\Core\BusinessLogic\DataAccess\PaymentMethod\Repositories\PaymentMethodRepository;
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
use SeQura\Core\BusinessLogic\Domain\Connection\RepositoryContracts\CredentialsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use SeQura\Core\BusinessLogic\Domain\Connection\Services\CredentialsService;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\RepositoryContracts\CountryConfigurationRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Services\CountryConfigurationService;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Services\SellingCountriesService;
use SeQura\Core\BusinessLogic\Domain\Deployments\ProxyContracts\DeploymentsProxyInterface;
use SeQura\Core\BusinessLogic\Domain\Deployments\RepositoryContracts\DeploymentsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Deployments\Services\DeploymentsService;
use SeQura\Core\BusinessLogic\Domain\Disconnect\Services\DisconnectService;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\RepositoryContracts\GeneralSettingsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Services\CategoryService;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Services\GeneralSettingsService;
use SeQura\Core\BusinessLogic\Domain\Integration\Category\CategoryServiceInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\Disconnect\DisconnectServiceInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\Order\MerchantDataProviderInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\Order\OrderCreationInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\OrderReport\OrderReportServiceInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\Product\ProductServiceInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\PromotionalWidgets\WidgetConfiguratorInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\SellingCountries\SellingCountriesServiceInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\ShopOrderStatuses\ShopOrderStatusesServiceInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\Store\StoreServiceInterface as IntegrationStoreService;
use SeQura\Core\BusinessLogic\Domain\Integration\Version\VersionServiceInterface as VersionStoreService;
use SeQura\Core\BusinessLogic\Domain\Merchant\ProxyContracts\MerchantProxyInterface;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\Order\Builders\MerchantOrderRequestBuilder;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Item\AbstractItemFactory;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Item\ItemFactory;
use SeQura\Core\BusinessLogic\Domain\Order\Models\SeQuraOrder;
use SeQura\Core\BusinessLogic\Domain\Order\ProxyContracts\OrderProxyInterface;
use SeQura\Core\BusinessLogic\Domain\Order\RepositoryContracts\SeQuraOrderRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Order\Service\OrderService;
use SeQura\Core\BusinessLogic\Domain\OrderReport\Listeners\TickEventListener;
use SeQura\Core\BusinessLogic\Domain\OrderReport\ProxyContracts\OrderReportProxyInterface;
use SeQura\Core\BusinessLogic\Domain\OrderReport\Service\OrderReportService;
use SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\RepositoryContracts\OrderStatusSettingsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\Services\OrderStatusSettingsService;
use SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\Services\ShopOrderStatusesService;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\RepositoryContracts\PaymentMethodRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Services\PaymentMethodsService;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\RepositoryContracts\WidgetSettingsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Services\WidgetSettingsService;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Services\WidgetValidationService;
use SeQura\Core\BusinessLogic\Domain\Integration\PromotionalWidgets\MiniWidgetMessagesProviderInterface;
use SeQura\Core\BusinessLogic\Domain\SendReport\RepositoryContracts\SendReportRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\StatisticalData\RepositoryContracts\StatisticalDataRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\StatisticalData\Services\StatisticalDataService;
use SeQura\Core\BusinessLogic\Domain\Stores\Services\StoreService;
use SeQura\Core\BusinessLogic\Domain\UIState\Services\UIStateService;
use SeQura\Core\BusinessLogic\Domain\Version\Services\VersionService;
use SeQura\Core\BusinessLogic\Domain\Webhook\Services\OrderStatusProvider;
use SeQura\Core\BusinessLogic\Providers\QueueNameProvider\Contract\QueueNameProviderInterface;
use SeQura\Core\BusinessLogic\Providers\QueueNameProvider\QueueNameProvider;
use SeQura\Core\BusinessLogic\SeQuraAPI\Connection\ConnectionProxy;
use SeQura\Core\BusinessLogic\SeQuraAPI\Deployments\DeploymentsProxy;
use SeQura\Core\BusinessLogic\SeQuraAPI\Factories\AuthorizedProxyFactory;
use SeQura\Core\BusinessLogic\SeQuraAPI\Factories\ConnectionProxyFactory;
use SeQura\Core\BusinessLogic\SeQuraAPI\Merchant\MerchantProxy;
use SeQura\Core\BusinessLogic\SeQuraAPI\Order\OrderProxy;
use SeQura\Core\BusinessLogic\SeQuraAPI\OrderReport\OrderReportProxy;
use SeQura\Core\BusinessLogic\TransactionLog\Listeners\AbortedListener;
use SeQura\Core\BusinessLogic\TransactionLog\Listeners\CreateListener;
use SeQura\Core\BusinessLogic\TransactionLog\Listeners\FailedListener;
use SeQura\Core\BusinessLogic\TransactionLog\Listeners\LoadListener;
use SeQura\Core\BusinessLogic\TransactionLog\Listeners\UpdateListener;
use SeQura\Core\BusinessLogic\TransactionLog\RepositoryContracts\TransactionLogRepositoryInterface;
use SeQura\Core\BusinessLogic\TransactionLog\Services\TransactionLogService;
use SeQura\Core\BusinessLogic\Webhook\Handler\WebhookHandler;
use SeQura\Core\BusinessLogic\Webhook\Services\ShopOrderService;
use SeQura\Core\BusinessLogic\Webhook\Validator\WebhookValidator;
use SeQura\Core\BusinessLogic\WebhookAPI\Controller\WebhookController;
use SeQura\Core\Infrastructure\BootstrapComponent as BaseBootstrapComponent;
use SeQura\Core\Infrastructure\Configuration\Configuration;
use SeQura\Core\Infrastructure\Http\HttpClient;
use SeQura\Core\Infrastructure\ORM\RepositoryRegistry;
use SeQura\Core\Infrastructure\ServiceRegister;
use SeQura\Core\Infrastructure\TaskExecution\Events\BaseQueueItemEvent;
use SeQura\Core\Infrastructure\TaskExecution\Events\QueueItemAbortedEvent;
use SeQura\Core\Infrastructure\TaskExecution\Events\QueueItemEnqueuedEvent;
use SeQura\Core\Infrastructure\TaskExecution\Events\QueueItemFailedEvent;
use SeQura\Core\Infrastructure\TaskExecution\Events\QueueItemFinishedEvent;
use SeQura\Core\Infrastructure\TaskExecution\Events\QueueItemRequeuedEvent;
use SeQura\Core\Infrastructure\TaskExecution\Events\QueueItemStartedEvent;
use SeQura\Core\Infrastructure\TaskExecution\Events\QueueItemStateTransitionEventBus;
use SeQura\Core\Infrastructure\TaskExecution\TaskEvents\TickEvent;
use SeQura\Core\Infrastructure\Utility\Events\EventBus;
use SeQura\Core\Infrastructure\Utility\TimeProvider;

class BootstrapComponent extends BaseBootstrapComponent
{
    /**
     * @return void
     */
    public static function init(): void
    {
        parent::init();

        static::initRepositories();
        static::initServices();
        static::initControllers();
        static::initProxies();
        static::initEvents();
    }

    /**
     * @inheritDoc
     */
    protected static function initRepositories(): void
    {
        parent::initRepositories();

        ServiceRegister::registerService(
            OrderStatusSettingsRepositoryInterface::class,
            static function () {
                return new OrderStatusMappingRepository(
                    RepositoryRegistry::getRepository(OrderStatusSettings::getClassName()),
                    ServiceRegister::getService(StoreContext::class)
                );
            }
        );

        ServiceRegister::registerService(
            ConnectionDataRepositoryInterface::class,
            static function () {
                return new ConnectionDataRepository(
                    RepositoryRegistry::getRepository(ConnectionData::getClassName()),
                    ServiceRegister::getService(StoreContext::class)
                );
            }
        );

        ServiceRegister::registerService(
            StatisticalDataRepositoryInterface::class,
            static function () {
                return new StatisticalDataRepository(
                    RepositoryRegistry::getRepository(StatisticalData::getClassName()),
                    ServiceRegister::getService(StoreContext::class)
                );
            }
        );

        ServiceRegister::registerService(
            CountryConfigurationRepositoryInterface::class,
            static function () {
                return new CountryConfigurationRepository(
                    RepositoryRegistry::getRepository(CountryConfiguration::getClassName()),
                    ServiceRegister::getService(StoreContext::class)
                );
            }
        );

        ServiceRegister::registerService(
            GeneralSettingsRepositoryInterface::class,
            static function () {
                return new GeneralSettingsRepository(
                    RepositoryRegistry::getRepository(GeneralSettings::getClassName()),
                    ServiceRegister::getService(StoreContext::class)
                );
            }
        );

        ServiceRegister::registerService(
            SeQuraOrderRepositoryInterface::class,
            static function () {
                return new SeQuraOrderRepository(
                    RepositoryRegistry::getRepository(SeQuraOrder::getClassName())
                );
            }
        );

        ServiceRegister::registerService(
            WidgetSettingsRepositoryInterface::class,
            static function () {
                return new WidgetSettingsRepository(
                    RepositoryRegistry::getRepository(WidgetSettings::getClassName()),
                    ServiceRegister::getService(StoreContext::class)
                );
            }
        );

        ServiceRegister::registerService(
            TransactionLogRepositoryInterface::class,
            static function () {
                return new TransactionLogRepository(
                    RepositoryRegistry::getRepository(TransactionLog::getClassName()),
                    ServiceRegister::getService(StoreContext::class)
                );
            }
        );

        ServiceRegister::registerService(
            SendReportRepositoryInterface::class,
            static function () {
                return new SendReportRepository(
                    RepositoryRegistry::getRepository(SendReport::getClassName()),
                    ServiceRegister::getService(StoreContext::class)
                );
            }
        );

        ServiceRegister::registerService(
            PaymentMethodRepositoryInterface::class,
            static function () {
                return new PaymentMethodRepository(
                    RepositoryRegistry::getRepository(DataAccess\PaymentMethod\Entities\PaymentMethod::getClassName()),
                    ServiceRegister::getService(StoreContext::class)
                );
            }
        );

        ServiceRegister::registerService(
            CredentialsRepositoryInterface::class,
            static function () {
                return new CredentialsRepository(
                    RepositoryRegistry::getRepository(Credentials::getClassName()),
                    ServiceRegister::getService(StoreContext::class)
                );
            }
        );

        ServiceRegister::registerService(
            DeploymentsRepositoryInterface::class,
            static function () {
                return new DeploymentRepository(
                    RepositoryRegistry::getRepository(Deployment::getClassName()),
                    ServiceRegister::getService(StoreContext::class)
                );
            }
        );
    }

    /**
     * @inheritDoc
     */
    protected static function initServices(): void
    {
        parent::initServices();

        ServiceRegister::registerService(StoreContext::class, static function () {
            return StoreContext::getInstance();
        });

        ServiceRegister::registerService(
            QueueNameProviderInterface::class,
            static function () {
                return new QueueNameProvider();
            }
        );

        ServiceRegister::registerService(
            OrderStatusSettingsService::class,
            static function () {
                return new OrderStatusSettingsService(
                    ServiceRegister::getService(OrderStatusSettingsRepositoryInterface::class),
                    ServiceRegister::getService(ShopOrderStatusesServiceInterface::class)
                );
            }
        );

        ServiceRegister::registerService(
            ConnectionService::class,
            static function () {
                return new ConnectionService(
                    ServiceRegister::getService(ConnectionDataRepositoryInterface::class),
                    ServiceRegister::getService(CredentialsService::class)
                );
            }
        );

        ServiceRegister::registerService(
            CredentialsService::class,
            static function () {
                return new CredentialsService(
                    ServiceRegister::getService(ConnectionProxyInterface::class),
                    ServiceRegister::getService(CredentialsRepositoryInterface::class),
                    ServiceRegister::getService(CountryConfigurationRepositoryInterface::class),
                    ServiceRegister::getService(PaymentMethodRepositoryInterface::class)
                );
            }
        );

        ServiceRegister::registerService(
            StatisticalDataService::class,
            static function () {
                return new StatisticalDataService(
                    ServiceRegister::getService(StatisticalDataRepositoryInterface::class),
                    ServiceRegister::getService(SendReportRepositoryInterface::class),
                    ServiceRegister::getService(TimeProvider::class)
                );
            }
        );

        ServiceRegister::registerService(
            CountryConfigurationService::class,
            static function () {
                return new CountryConfigurationService(
                    ServiceRegister::getService(CountryConfigurationRepositoryInterface::class),
                    ServiceRegister::getService(SellingCountriesService::class)
                );
            }
        );

        ServiceRegister::registerService(
            UIStateService::class,
            static function () {
                return new UIStateService(
                    ServiceRegister::getService(ConnectionService::class),
                    ServiceRegister::getService(ConnectionDataRepositoryInterface::class),
                    ServiceRegister::getService(CountryConfigurationRepositoryInterface::class),
                    ServiceRegister::getService(WidgetSettingsRepositoryInterface::class)
                );
            }
        );

        ServiceRegister::registerService(
            PaymentMethodsService::class,
            static function () {
                return new PaymentMethodsService(
                    ServiceRegister::getService(MerchantProxyInterface::class),
                    ServiceRegister::getService(PaymentMethodRepositoryInterface::class),
                    ServiceRegister::getService(CountryConfigurationService::class)
                );
            }
        );

        ServiceRegister::registerService(
            GeneralSettingsService::class,
            static function () {
                return new GeneralSettingsService(
                    ServiceRegister::getService(GeneralSettingsRepositoryInterface::class)
                );
            }
        );

        ServiceRegister::registerService(
            SellingCountriesService::class,
            static function () {
                return new SellingCountriesService(
                    ServiceRegister::getService(SellingCountriesServiceInterface::class),
                    ServiceRegister::getService(ConnectionService::class)
                );
            }
        );

        ServiceRegister::registerService(
            StoreService::class,
            static function () {
                return new StoreService(
                    ServiceRegister::getService(IntegrationStoreService::class),
                    ServiceRegister::getService(ConnectionDataRepositoryInterface::class)
                );
            }
        );

        ServiceRegister::registerService(
            VersionService::class,
            static function () {
                return new VersionService(
                    ServiceRegister::getService(VersionStoreService::class)
                );
            }
        );

        ServiceRegister::registerService(
            CategoryService::class,
            static function () {
                return new CategoryService(
                    ServiceRegister::getService(CategoryServiceInterface::class)
                );
            }
        );

        ServiceRegister::registerService(
            ShopOrderStatusesService::class,
            static function () {
                return new ShopOrderStatusesService(
                    ServiceRegister::getService(ShopOrderStatusesServiceInterface::class)
                );
            }
        );

        ServiceRegister::registerService(
            DisconnectService::class,
            static function () {
                return new DisconnectService(
                    ServiceRegister::getService(DisconnectServiceInterface::class),
                    ServiceRegister::getService(SendReportRepositoryInterface::class),
                    ServiceRegister::getService(ConnectionDataRepositoryInterface::class),
                    ServiceRegister::getService(CredentialsRepositoryInterface::class),
                    ServiceRegister::getService(CountryConfigurationRepositoryInterface::class),
                    ServiceRegister::getService(DeploymentsRepositoryInterface::class),
                    ServiceRegister::getService(GeneralSettingsRepositoryInterface::class),
                    ServiceRegister::getService(SeQuraOrderRepositoryInterface::class),
                    ServiceRegister::getService(OrderStatusSettingsRepositoryInterface::class),
                    ServiceRegister::getService(PaymentMethodRepositoryInterface::class),
                    ServiceRegister::getService(WidgetSettingsRepositoryInterface::class),
                    ServiceRegister::getService(StatisticalDataRepositoryInterface::class),
                    ServiceRegister::getService(TransactionLogRepositoryInterface::class)
                );
            }
        );

        ServiceRegister::registerService(
            WebhookHandler::class,
            static function () {
                return new WebhookHandler(
                    ServiceRegister::getService(ShopOrderService::class),
                    ServiceRegister::getService(MerchantOrderRequestBuilder::class)
                );
            }
        );

        ServiceRegister::registerService(
            OrderStatusProvider::class,
            static function () {
                return ServiceRegister::getService(OrderStatusSettingsService::class);
            }
        );

        ServiceRegister::registerService(
            WebhookValidator::class,
            static function () {
                return new WebhookValidator();
            }
        );

        ServiceRegister::registerService(
            OrderService::class,
            static function () {
                return new OrderService(
                    ServiceRegister::getService(OrderProxyInterface::class),
                    ServiceRegister::getService(SeQuraOrderRepositoryInterface::class),
                    ServiceRegister::getService(MerchantOrderRequestBuilder::class),
                    ServiceRegister::getService(OrderCreationInterface::class)
                );
            }
        );

        ServiceRegister::registerService(
            OrderReportService::class,
            static function () {
                return new OrderReportService(
                    ServiceRegister::getService(OrderReportProxyInterface::class),
                    ServiceRegister::getService(OrderReportServiceInterface::class),
                    ServiceRegister::getService(SendReportRepositoryInterface::class)
                );
            }
        );

        ServiceRegister::registerService(
            WidgetSettingsService::class,
            static function () {
                return new WidgetSettingsService(
                    ServiceRegister::getService(WidgetSettingsRepositoryInterface::class),
                    ServiceRegister::getService(PaymentMethodsService::class),
                    ServiceRegister::getService(CredentialsService::class),
                    ServiceRegister::getService(ConnectionService::class),
                    ServiceRegister::getService(WidgetConfiguratorInterface::class),
                    ServiceRegister::getService(MiniWidgetMessagesProviderInterface::class),
                    ServiceRegister::getService(DeploymentsService::class)
                );
            }
        );

        ServiceRegister::registerService(
            WidgetValidationService::class,
            static function () {
                return new WidgetValidationService(
                    ServiceRegister::getService(GeneralSettingsService::class),
                    ServiceRegister::getService(ProductServiceInterface::class)
                );
            }
        );

        ServiceRegister::registerService(
            TransactionLogService::class,
            static function () {
                return new TransactionLogService(
                    ServiceRegister::getService(TransactionLogRepositoryInterface::class),
                    ServiceRegister::getService(OrderService::class),
                    ServiceRegister::getService(ShopOrderService::class)
                );
            }
        );

        ServiceRegister::registerService(
            AbstractItemFactory::class,
            static function () {
                return new ItemFactory();
            }
        );

        ServiceRegister::registerService(
            DeploymentsService::class,
            static function () {
                return new DeploymentsService(
                    ServiceRegister::getService(DeploymentsProxyInterface::class),
                    ServiceRegister::getService(DeploymentsRepositoryInterface::class),
                    ServiceRegister::getService(ConnectionDataRepositoryInterface::class)
                );
            }
        );

        ServiceRegister::registerService(
            MerchantOrderRequestBuilder::class,
            static function () {
                return new MerchantOrderRequestBuilder(
                    ServiceRegister::getService(ConnectionService::class),
                    ServiceRegister::getService(CredentialsService::class),
                    ServiceRegister::getService(MerchantDataProviderInterface::class)
                );
            }
        );
    }

    /**
     * Initializes API facade controllers.
     *
     * @return void
     */
    protected static function initControllers(): void
    {
        ServiceRegister::registerService(
            WebhookController::class,
            static function () {
                return new WebhookController(
                    ServiceRegister::getService(WebhookValidator::class),
                    ServiceRegister::getService(WebhookHandler::class)
                );
            }
        );

        ServiceRegister::registerService(
            ConnectionController::class,
            static function () {
                return new ConnectionController(
                    ServiceRegister::getService(ConnectionService::class),
                    ServiceRegister::getService(StatisticalDataService::class)
                );
            }
        );

        ServiceRegister::registerService(
            CountryConfigurationController::class,
            static function () {
                return new CountryConfigurationController(
                    ServiceRegister::getService(CountryConfigurationService::class),
                    ServiceRegister::getService(SellingCountriesService::class)
                );
            }
        );

        ServiceRegister::registerService(
            StoreController::class,
            static function () {
                return new StoreController(
                    ServiceRegister::getService(StoreService::class)
                );
            }
        );

        ServiceRegister::registerService(
            GeneralSettingsController::class,
            static function () {
                return new GeneralSettingsController(
                    ServiceRegister::getService(GeneralSettingsService::class),
                    ServiceRegister::getService(CategoryService::class)
                );
            }
        );

        ServiceRegister::registerService(
            OrderStatusSettingsController::class,
            static function () {
                return new OrderStatusSettingsController(
                    ServiceRegister::getService(OrderStatusSettingsService::class),
                    ServiceRegister::getService(ShopOrderStatusesService::class)
                );
            }
        );

        ServiceRegister::registerService(
            PaymentMethodsController::class,
            static function () {
                return new PaymentMethodsController(
                    ServiceRegister::getService(PaymentMethodsService::class)
                );
            }
        );

        ServiceRegister::registerService(
            PromotionalWidgetsController::class,
            static function () {
                return new PromotionalWidgetsController(
                    ServiceRegister::getService(WidgetSettingsService::class)
                );
            }
        );

        ServiceRegister::registerService(
            TransactionLogsController::class,
            static function () {
                return new TransactionLogsController(
                    ServiceRegister::getService(TransactionLogService::class)
                );
            }
        );

        ServiceRegister::registerService(
            IntegrationController::class,
            static function () {
                return new IntegrationController(
                    ServiceRegister::getService(VersionService::class),
                    ServiceRegister::getService(Configuration::class),
                    ServiceRegister::getService(UIStateService::class)
                );
            }
        );

        ServiceRegister::registerService(
            DisconnectController::class,
            static function () {
                return new DisconnectController(
                    ServiceRegister::getService(DisconnectService::class)
                );
            }
        );

        ServiceRegister::registerService(
            SolicitationController::class,
            static function () {
                return new SolicitationController(ServiceRegister::getService(OrderService::class));
            }
        );

        ServiceRegister::registerService(
            CachedPaymentMethodsController::class,
            static function () {
                return new CachedPaymentMethodsController(ServiceRegister::getService(PaymentMethodsService::class));
            }
        );

        ServiceRegister::registerService(
            PromotionalWidgetsCheckoutController::class,
            static function () {
                return new PromotionalWidgetsCheckoutController(
                    ServiceRegister::getService(WidgetSettingsService::class),
                    ServiceRegister::getService(WidgetValidationService::class)
                );
            }
        );

        ServiceRegister::registerService(
            DeploymentsController::class,
            static function () {
                return new DeploymentsController(
                    ServiceRegister::getService(DeploymentsService::class)
                );
            }
        );
    }

    /**
     * Initializes API facade proxies.
     *
     * @return void
     */
    protected static function initProxies(): void
    {
        ServiceRegister::registerService(
            AuthorizedProxyFactory::class,
            static function () {
                return new AuthorizedProxyFactory(
                    ServiceRegister::getService(HttpClient::class),
                    ServiceRegister::getService(ConnectionService::class),
                    ServiceRegister::getService(DeploymentsService::class)
                );
            }
        );

        ServiceRegister::registerService(
            ConnectionProxyFactory::class,
            static function () {
                return new ConnectionProxyFactory(
                    ServiceRegister::getService(HttpClient::class),
                    ServiceRegister::getService(DeploymentsService::class)
                );
            }
        );

        ServiceRegister::registerService(
            OrderProxyInterface::class,
            static function () {
                return new OrderProxy(
                    ServiceRegister::getService(AuthorizedProxyFactory::class)
                );
            }
        );

        ServiceRegister::registerService(
            OrderReportProxyInterface::class,
            static function () {
                return new OrderReportProxy(
                    ServiceRegister::getService(AuthorizedProxyFactory::class)
                );
            }
        );

        ServiceRegister::registerService(
            MerchantProxyInterface::class,
            static function () {
                return new MerchantProxy(
                    ServiceRegister::getService(AuthorizedProxyFactory::class)
                );
            }
        );

        ServiceRegister::registerService(
            ConnectionProxyInterface::class,
            static function () {
                return new ConnectionProxy(
                    ServiceRegister::getService(ConnectionProxyFactory::class)
                );
            }
        );

        ServiceRegister::registerService(
            DeploymentsProxyInterface::class,
            static function () {
                return new DeploymentsProxy(
                    ServiceRegister::getService(HttpClient::class)
                );
            }
        );
    }

    /**
     * @inheritDoc
     */
    protected static function initEvents(): void
    {
        parent::initEvents();

        EventBus::getInstance()->when(TickEvent::class, TickEventListener::class . '::handle');

        /**
         * @var QueueItemStateTransitionEventBus $queueBus
         */
        $queueBus = ServiceRegister::getService(QueueItemStateTransitionEventBus::CLASS_NAME);

        $queueBus->when(
            QueueItemEnqueuedEvent::class,
            static function (BaseQueueItemEvent $event) {
                (new CreateListener(ServiceRegister::getService(TransactionLogService::class)))->handle($event);
            }
        );

        $queueBus->when(
            QueueItemRequeuedEvent::class,
            static function (BaseQueueItemEvent $event) {
                (new CreateListener(ServiceRegister::getService(TransactionLogService::class)))->handle($event);
            }
        );

        $queueBus->when(
            QueueItemStartedEvent::class,
            static function (BaseQueueItemEvent $event) {
                (new LoadListener(ServiceRegister::getService(TransactionLogService::class)))->handle($event);
            }
        );

        $queueBus->when(
            QueueItemStartedEvent::class,
            static function (BaseQueueItemEvent $event) {
                (new UpdateListener(ServiceRegister::getService(TransactionLogService::class)))->handle($event);
            }
        );

        $queueBus->when(
            QueueItemFinishedEvent::class,
            static function (BaseQueueItemEvent $event) {
                (new UpdateListener(ServiceRegister::getService(TransactionLogService::class)))->handle($event);
            }
        );

        $queueBus->when(
            QueueItemFailedEvent::class,
            static function (BaseQueueItemEvent $event) {
                (new FailedListener(ServiceRegister::getService(TransactionLogService::class)))->handle($event);
            }
        );

        $queueBus->when(
            QueueItemAbortedEvent::class,
            static function (BaseQueueItemEvent $event) {
                (new AbortedListener(ServiceRegister::getService(TransactionLogService::class)))->handle($event);
            }
        );
    }
}
