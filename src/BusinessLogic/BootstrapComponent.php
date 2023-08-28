<?php

namespace SeQura\Core\BusinessLogic;

use SeQura\Core\BusinessLogic\AdminAPI\Connection\ConnectionController;
use SeQura\Core\BusinessLogic\AdminAPI\CountryConfiguration\CountryConfigurationController;
use SeQura\Core\BusinessLogic\AdminAPI\Disconnect\DisconnectController;
use SeQura\Core\BusinessLogic\AdminAPI\GeneralSettings\GeneralSettingsController;
use SeQura\Core\BusinessLogic\AdminAPI\Integration\IntegrationController;
use SeQura\Core\BusinessLogic\AdminAPI\OrderStatusSettings\OrderStatusSettingsController;
use SeQura\Core\BusinessLogic\AdminAPI\PaymentMethods\PaymentMethodsController;
use SeQura\Core\BusinessLogic\AdminAPI\PromotionalWidgets\PromotionalWidgetsController;
use SeQura\Core\BusinessLogic\AdminAPI\Store\StoreController;
use SeQura\Core\BusinessLogic\CheckoutAPI\Solicitation\Controller\SolicitationController;
use SeQura\Core\BusinessLogic\DataAccess\ConnectionData\Entities\ConnectionData;
use SeQura\Core\BusinessLogic\DataAccess\ConnectionData\Repositories\ConnectionDataRepository;
use SeQura\Core\BusinessLogic\DataAccess\CountryConfiguration\Entities\CountryConfiguration;
use SeQura\Core\BusinessLogic\DataAccess\CountryConfiguration\Repositories\CountryConfigurationRepository;
use SeQura\Core\BusinessLogic\DataAccess\GeneralSettings\Entities\GeneralSettings;
use SeQura\Core\BusinessLogic\DataAccess\GeneralSettings\Repositories\GeneralSettingsRepository;
use SeQura\Core\BusinessLogic\DataAccess\Order\Repositories\SeQuraOrderRepository;
use SeQura\Core\BusinessLogic\DataAccess\OrderSettings\Entities\OrderStatusMapping;
use SeQura\Core\BusinessLogic\DataAccess\OrderSettings\Repositories\OrderStatusMappingRepository;
use SeQura\Core\BusinessLogic\DataAccess\PromotionalWidgets\Entities\WidgetSettings;
use SeQura\Core\BusinessLogic\DataAccess\PromotionalWidgets\Repositories\WidgetSettingsRepository;
use SeQura\Core\BusinessLogic\DataAccess\StatisticalData\Entities\StatisticalData;
use SeQura\Core\BusinessLogic\DataAccess\StatisticalData\Repositories\StatisticalDataRepository;
use SeQura\Core\BusinessLogic\Domain\Connection\ProxyContracts\ConnectionProxyInterface;
use SeQura\Core\BusinessLogic\Domain\Connection\RepositoryContracts\ConnectionDataRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\RepositoryContracts\CountryConfigurationRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Services\CountryConfigurationService;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Services\SellingCountriesService;
use SeQura\Core\BusinessLogic\Domain\Disconnect\Services\DisconnectService;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\RepositoryContracts\GeneralSettingsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Services\CategoryService;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Services\GeneralSettingsService;
use SeQura\Core\BusinessLogic\Domain\Integration\Category\CategoryServiceInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\Disconnect\DisconnectServiceInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\OrderReport\OrderReportServiceInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\SellingCountries\SellingCountriesServiceInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\Store\StoreServiceInterface as IntegrationStoreService;
use SeQura\Core\BusinessLogic\Domain\Integration\Version\VersionServiceInterface as VersionStoreService;
use SeQura\Core\BusinessLogic\Domain\Merchant\ProxyContracts\MerchantProxyInterface;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\Order\Models\SeQuraOrder;
use SeQura\Core\BusinessLogic\Domain\Order\ProxyContracts\OrderProxyInterface;
use SeQura\Core\BusinessLogic\Domain\Order\RepositoryContracts\SeQuraOrderRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Order\Service\OrderService;
use SeQura\Core\BusinessLogic\Domain\OrderReport\ProxyContracts\OrderReportProxyInterface;
use SeQura\Core\BusinessLogic\Domain\OrderReport\Service\OrderReportService;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Services\PaymentMethodsService;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\RepositoryContracts\WidgetSettingsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Services\WidgetSettingsService;
use SeQura\Core\BusinessLogic\Domain\StatisticalData\RepositoryContracts\StatisticalDataRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\StatisticalData\Services\StatisticalDataService;
use SeQura\Core\BusinessLogic\Domain\Stores\Services\StoreService;
use SeQura\Core\BusinessLogic\Domain\UIState\Services\UIStateService;
use SeQura\Core\BusinessLogic\Domain\Version\Services\VersionService;
use SeQura\Core\BusinessLogic\Domain\Webhook\Services\OrderStatusProvider;
use SeQura\Core\BusinessLogic\Providers\QueueNameProvider\Contract\QueueNameProviderInterface;
use SeQura\Core\BusinessLogic\Providers\QueueNameProvider\QueueNameProvider;
use SeQura\Core\BusinessLogic\SeQuraAPI\Connection\ConnectionProxy;
use SeQura\Core\BusinessLogic\SeQuraAPI\Merchant\MerchantProxy;
use SeQura\Core\BusinessLogic\SeQuraAPI\Order\OrderProxy;
use SeQura\Core\BusinessLogic\SeQuraAPI\OrderReport\OrderReportProxy;
use SeQura\Core\BusinessLogic\Webhook\Handler\WebhookHandler;
use SeQura\Core\BusinessLogic\Webhook\Services\StatusMappingService;
use SeQura\Core\BusinessLogic\Webhook\Validator\WebhookValidator;
use SeQura\Core\BusinessLogic\WebhookAPI\Controller\WebhookController;
use SeQura\Core\Infrastructure\BootstrapComponent as BaseBootstrapComponent;
use SeQura\Core\BusinessLogic\Webhook\Repositories\OrderStatusMappingRepository as OrderStatusMappingRepositoryInterface;
use SeQura\Core\Infrastructure\Configuration\Configuration;
use SeQura\Core\Infrastructure\Http\HttpClient;
use SeQura\Core\Infrastructure\ORM\RepositoryRegistry;
use SeQura\Core\Infrastructure\ServiceRegister;
use SeQura\Core\Infrastructure\TaskExecution\QueueService;

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
    }

    /**
     * @inheritDoc
     */
    protected static function initRepositories(): void
    {
        parent::initRepositories();

        ServiceRegister::registerService(
            OrderStatusMappingRepositoryInterface::class,
            static function () {
                return new OrderStatusMappingRepository(
                    RepositoryRegistry::getRepository(OrderStatusMapping::getClassName()),
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
            StatusMappingService::class,
            static function () {
                return new StatusMappingService(
                    ServiceRegister::getService(OrderStatusMappingRepositoryInterface::class)
                );
            }
        );

        ServiceRegister::registerService(
            ConnectionService::class,
            static function () {
                return new ConnectionService(
                    ServiceRegister::getService(ConnectionProxyInterface::class)
                );
            }
        );

        ServiceRegister::registerService(
            StatisticalDataService::class,
            static function () {
                return new StatisticalDataService(
                    ServiceRegister::getService(StatisticalDataRepositoryInterface::class)
                );
            }
        );

        ServiceRegister::registerService(
            CountryConfigurationService::class,
            static function () {
                return new CountryConfigurationService(
                    ServiceRegister::getService(CountryConfigurationRepositoryInterface::class)
                );
            }
        );

        ServiceRegister::registerService(
            UIStateService::class,
            static function () {
                return new UIStateService(
                    ServiceRegister::getService(ConnectionDataRepositoryInterface::class),
                    ServiceRegister::getService(CountryConfigurationRepositoryInterface::class)
                );
            }
        );

        ServiceRegister::registerService(
            PaymentMethodsService::class,
            static function () {
                return new PaymentMethodsService(
                    ServiceRegister::getService(MerchantProxyInterface::class)
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
                    ServiceRegister::getService(SellingCountriesServiceInterface::class)
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
            DisconnectService::class,
            static function () {
                return new DisconnectService(
                    ServiceRegister::getService(DisconnectServiceInterface::class)
                );
            }
        );

        ServiceRegister::registerService(
            WebhookHandler::class,
            static function () {
                return new WebhookHandler(
                    ServiceRegister::getService(QueueService::class),
                    ServiceRegister::getService(QueueNameProviderInterface::class)
                );
            }
        );

        ServiceRegister::registerService(
            OrderStatusProvider::class,
            static function () {
                return ServiceRegister::getService(StatusMappingService::class);
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
                    ServiceRegister::getService(SeQuraOrderRepositoryInterface::class)
                );
            }
        );

        ServiceRegister::registerService(
            OrderReportService::class,
            static function () {
                return new OrderReportService(
                    ServiceRegister::getService(OrderReportProxyInterface::class),
                    ServiceRegister::getService(OrderReportServiceInterface::class)
                );
            }
        );

        ServiceRegister::registerService(
            WidgetSettingsService::class,
            static function () {
                return new WidgetSettingsService(
                    ServiceRegister::getService(WidgetSettingsRepositoryInterface::class)
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
                return new OrderStatusSettingsController();
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
    }

    /**
     * Initializes API facade proxies.
     *
     * @return void
     */
    protected static function initProxies(): void
    {
        ServiceRegister::registerService(
            OrderProxyInterface::class,
            static function () {
                return new OrderProxy(
                    ServiceRegister::getService(HttpClient::class),
                    ServiceRegister::getService(ConnectionDataRepositoryInterface::class)
                );
            }
        );

        ServiceRegister::registerService(
            OrderReportProxyInterface::class,
            static function () {
                return new OrderReportProxy(
                    ServiceRegister::getService(HttpClient::class),
                    ServiceRegister::getService(ConnectionDataRepositoryInterface::class)
                );
            }
        );

        ServiceRegister::registerService(
            MerchantProxyInterface::class,
            static function () {
                return new MerchantProxy(
                    ServiceRegister::getService(HttpClient::class),
                    ServiceRegister::getService(ConnectionDataRepositoryInterface::class)
                );
            }
        );

        ServiceRegister::registerService(
            ConnectionProxyInterface::class,
            static function () {
                return new ConnectionProxy(
                    ServiceRegister::getService(HttpClient::class)
                );
            }
        );
    }
}
