<?php

namespace SeQura\Core\BusinessLogic;

use SeQura\Core\BusinessLogic\AdminAPI\Connection\ConnectionController;
use SeQura\Core\BusinessLogic\AdminAPI\CountryConfiguration\CountryConfigurationController;
use SeQura\Core\BusinessLogic\AdminAPI\GeneralSettings\GeneralSettingsController;
use SeQura\Core\BusinessLogic\AdminAPI\Integration\IntegrationController;
use SeQura\Core\BusinessLogic\AdminAPI\OrderStatusSettings\OrderStatusSettingsController;
use SeQura\Core\BusinessLogic\AdminAPI\PaymentMethods\PaymentMethodsController;
use SeQura\Core\BusinessLogic\AdminAPI\Store\StoreController;
use SeQura\Core\BusinessLogic\AdminAPI\WidgetConfiguration\WidgetConfigurationController;
use SeQura\Core\BusinessLogic\DataAccess\ConnectionData\Entities\ConnectionData;
use SeQura\Core\BusinessLogic\DataAccess\ConnectionData\Repositories\ConnectionDataRepository;
use SeQura\Core\BusinessLogic\DataAccess\CountryConfiguration\Entities\CountryConfiguration;
use SeQura\Core\BusinessLogic\DataAccess\CountryConfiguration\Repositories\CountryConfigurationRepository;
use SeQura\Core\BusinessLogic\DataAccess\OrderSettings\Entities\OrderStatusMapping;
use SeQura\Core\BusinessLogic\DataAccess\OrderSettings\Repositories\OrderStatusMappingRepository;
use SeQura\Core\BusinessLogic\Domain\Connection\ProxyContracts\ConnectionProxyInterface;
use SeQura\Core\BusinessLogic\Domain\Connection\RepositoryContracts\ConnectionDataRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\RepositoryContracts\CountryConfigurationRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Services\CountryConfigurationService;
use SeQura\Core\BusinessLogic\Domain\Integration\Store\StoreServiceInterface as IntegrationStoreService;
use SeQura\Core\BusinessLogic\Domain\Merchant\ProxyContracts\MerchantProxyInterface;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\Order\ProxyContracts\OrderProxyInterface;
use SeQura\Core\BusinessLogic\Domain\Stores\Services\StoreService;
use SeQura\Core\BusinessLogic\Domain\Webhook\Services\OrderStatusProvider;
use SeQura\Core\BusinessLogic\Providers\QueueNameProvider\Contract\QueueNameProviderInterface;
use SeQura\Core\BusinessLogic\Providers\QueueNameProvider\QueueNameProvider;
use SeQura\Core\BusinessLogic\SeQuraAPI\Connection\ConnectionProxy;
use SeQura\Core\BusinessLogic\SeQuraAPI\Merchant\MerchantProxy;
use SeQura\Core\BusinessLogic\SeQuraAPI\Order\OrderProxy;
use SeQura\Core\BusinessLogic\Webhook\Handler\WebhookHandler;
use SeQura\Core\BusinessLogic\Webhook\Services\StatusMappingService;
use SeQura\Core\BusinessLogic\Webhook\Validator\WebhookValidator;
use SeQura\Core\BusinessLogic\WebhookAPI\Controller\WebhookController;
use SeQura\Core\Infrastructure\BootstrapComponent as BaseBootstrapComponent;
use SeQura\Core\BusinessLogic\Webhook\Repositories\OrderStatusMappingRepository as OrderStatusMappingRepositoryInterface;
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
            CountryConfigurationRepositoryInterface::class,
            static function () {
                return new CountryConfigurationRepository(
                    RepositoryRegistry::getRepository(CountryConfiguration::getClassName()),
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
            CountryConfigurationService::class,
            static function () {
                return new CountryConfigurationService();
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
                    ServiceRegister::getService(ConnectionService::class)
                );
            }
        );

        ServiceRegister::registerService(
            CountryConfigurationController::class,
            static function () {
                return new CountryConfigurationController(
                    ServiceRegister::getService(CountryConfigurationService::class)
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
                return new GeneralSettingsController();
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
                return new PaymentMethodsController();
            }
        );

        ServiceRegister::registerService(
            WidgetConfigurationController::class,
            static function () {
                return new WidgetConfigurationController();
            }
        );

        ServiceRegister::registerService(
            IntegrationController::class,
            static function () {
                return new IntegrationController();
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
