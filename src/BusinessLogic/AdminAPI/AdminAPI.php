<?php

namespace SeQura\Core\BusinessLogic\AdminAPI;

use SeQura\Core\BusinessLogic\AdminAPI\Aspects\ErrorHandlingAspect;
use SeQura\Core\BusinessLogic\AdminAPI\Aspects\StoreContextAspect;
use SeQura\Core\BusinessLogic\AdminAPI\Connection\ConnectionController;
use SeQura\Core\BusinessLogic\AdminAPI\CountryConfiguration\CountryConfigurationController;
use SeQura\Core\BusinessLogic\AdminAPI\Disconnect\DisconnectController;
use SeQura\Core\BusinessLogic\AdminAPI\GeneralSettings\GeneralSettingsController;
use SeQura\Core\BusinessLogic\AdminAPI\Integration\IntegrationController;
use SeQura\Core\BusinessLogic\AdminAPI\OrderStatusSettings\OrderStatusSettingsController;
use SeQura\Core\BusinessLogic\AdminAPI\PaymentMethods\PaymentMethodsController;
use SeQura\Core\BusinessLogic\AdminAPI\PromotionalWidgets\PromotionalWidgetsController;
use SeQura\Core\BusinessLogic\AdminAPI\Store\StoreController;
use SeQura\Core\BusinessLogic\AdminAPI\TransactionLogs\TransactionLogsController;
use SeQura\Core\BusinessLogic\Bootstrap\Aspect\Aspects;

/**
 * Class AdminAPI. Integrations should use this class for communicating with Admin API.
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI
 */
class AdminAPI
{
    protected function __construct()
    {
    }

    /**
     * Gets an AdminAPI instance.
     *
     * @return AdminAPI
     */
    public static function get(): object
    {
        return Aspects::run(new ErrorHandlingAspect())->beforeEachMethodOfInstance(new AdminAPI());
    }


    /**
     * Returns a ConnectionController instance.
     *
     * @param string $storeId
     *
     * @return ConnectionController
     */
    public function connection(string $storeId): object
    {
        return Aspects
            ::run(new ErrorHandlingAspect())
            ->andRun(new StoreContextAspect($storeId))
            ->beforeEachMethodOfService(ConnectionController::class);
    }

    /**
     * Returns a StoreController instance.
     *
     * @param string $storeId
     *
     * @return StoreController
     */
    public function store(string $storeId): object
    {
        return Aspects
            ::run(new ErrorHandlingAspect())
            ->andRun(new StoreContextAspect($storeId))
            ->beforeEachMethodOfService(StoreController::class);
    }

    /**
     * Returns a CountryConfigurationController instance.
     *
     * @param string $storeId
     *
     * @return CountryConfigurationController
     */
    public function countryConfiguration(string $storeId): object
    {
        return Aspects
            ::run(new ErrorHandlingAspect())
            ->andRun(new StoreContextAspect($storeId))
            ->beforeEachMethodOfService(CountryConfigurationController::class);
    }

    /**
     * Returns a PromotionalWidgetsController instance.
     *
     * @param string $storeId
     *
     * @return PromotionalWidgetsController
     */
    public function widgetConfiguration(string $storeId): object
    {
        return Aspects
            ::run(new ErrorHandlingAspect())
            ->andRun(new StoreContextAspect($storeId))
            ->beforeEachMethodOfService(PromotionalWidgetsController::class);
    }

    /**
     * Returns a PaymentMethodsController instance.
     *
     * @param string $storeId
     *
     * @return PaymentMethodsController
     */
    public function paymentMethods(string $storeId): object
    {
        return Aspects
            ::run(new ErrorHandlingAspect())
            ->andRun(new StoreContextAspect($storeId))
            ->beforeEachMethodOfService(PaymentMethodsController::class);
    }

    /**
     * Returns a GeneralSettingsController instance.
     *
     * @param string $storeId
     *
     * @return GeneralSettingsController
     */
    public function generalSettings(string $storeId): object
    {
        return Aspects
            ::run(new ErrorHandlingAspect())
            ->andRun(new StoreContextAspect($storeId))
            ->beforeEachMethodOfService(GeneralSettingsController::class);
    }

    /**
     * Returns a OrderStatusSettingsController instance.
     *
     * @param string $storeId
     *
     * @return OrderStatusSettingsController
     */
    public function orderStatusSettings(string $storeId): object
    {
        return Aspects
            ::run(new ErrorHandlingAspect())
            ->andRun(new StoreContextAspect($storeId))
            ->beforeEachMethodOfService(OrderStatusSettingsController::class);
    }

    /**
     * Returns a TransactionLogsController instance.
     *
     * @param string $storeId
     *
     * @return TransactionLogsController
     */
    public function transactionLogs(string $storeId): object
    {
        return Aspects
            ::run(new ErrorHandlingAspect())
            ->andRun(new StoreContextAspect($storeId))
            ->beforeEachMethodOfService(TransactionLogsController::class);
    }

    /**
     * Returns a IntegrationController instance.
     *
     * @param string $storeId
     *
     * @return IntegrationController
     */
    public function integration(string $storeId): object
    {
        return Aspects
            ::run(new ErrorHandlingAspect())
            ->andRun(new StoreContextAspect($storeId))
            ->beforeEachMethodOfService(IntegrationController::class);
    }

    /**
     * Returns a DisconnectController instance.
     *
     * @param string $storeId
     *
     * @return DisconnectController
     */
    public function disconnect(string $storeId): object
    {
        return Aspects
            ::run(new ErrorHandlingAspect())
            ->andRun(new StoreContextAspect($storeId))
            ->beforeEachMethodOfService(DisconnectController::class);
    }
}
