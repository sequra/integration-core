<?php

namespace SeQura\Core\BusinessLogic\Domain\Disconnect\Services;

use SeQura\Core\BusinessLogic\Domain\Connection\RepositoryContracts\ConnectionDataRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Connection\RepositoryContracts\CredentialsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\RepositoryContracts\CountryConfigurationRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Deployments\RepositoryContracts\DeploymentsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\RepositoryContracts\GeneralSettingsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\Disconnect\DisconnectServiceInterface;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\Order\RepositoryContracts\SeQuraOrderRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\RepositoryContracts\OrderStatusSettingsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Exceptions\PaymentMethodNotFoundException;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\RepositoryContracts\PaymentMethodRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\RepositoryContracts\WidgetSettingsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\SendReport\RepositoryContracts\SendReportRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\StatisticalData\RepositoryContracts\StatisticalDataRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Services\StoreIntegrationService;
use SeQura\Core\BusinessLogic\TransactionLog\RepositoryContracts\TransactionLogRepositoryInterface;

/**
 * Class DisconnectService
 *
 * @package SeQura\Core\BusinessLogic\Domain\Disconnect\Services
 */
class DisconnectService
{
    /**
     * @var DisconnectServiceInterface $integrationDisconnectService
     */
    protected $integrationDisconnectService;

    /**
     * @var SendReportRepositoryInterface $sendReportRepository
     */
    protected $sendReportRepository;

    /**
     * @var ConnectionDataRepositoryInterface $connectionDataRepository
     */
    protected $connectionDataRepository;

    /**
     * @var CredentialsRepositoryInterface $credentialsRepository
     */
    protected $credentialsRepository;

    /**
     * @var CountryConfigurationRepositoryInterface $countryConfigurationRepository
     */
    protected $countryConfigurationRepository;

    /**
     * @var DeploymentsRepositoryInterface $deploymentsRepository
     */
    protected $deploymentsRepository;

    /**
     * @var GeneralSettingsRepositoryInterface $generalSettingsRepository
     */
    protected $generalSettingsRepository;

    /**
     * @var SeQuraOrderRepositoryInterface $sequraOrderRepository
     */
    protected $sequraOrderRepository;

    /**
     * @var OrderStatusSettingsRepositoryInterface $orderStatusSettingsRepository
     */
    protected $orderStatusSettingsRepository;

    /**
     * @var PaymentMethodRepositoryInterface $paymentMethodRepository
     */
    protected $paymentMethodRepository;

    /**
     * @var WidgetSettingsRepositoryInterface $widgetSettingsRepository
     */
    protected $widgetSettingsRepository;

    /**
     * @var StatisticalDataRepositoryInterface $statisticalDataRepository
     */
    protected $statisticalDataRepository;

    /**
     * @var TransactionLogRepositoryInterface $transactionLogRepository
     */
    protected $transactionLogRepository;

    /**
     * @var StoreIntegrationService $storeIntegrationService
     */
    protected $storeIntegrationService;

    /**
     * @param DisconnectServiceInterface $integrationDisconnectService
     * @param SendReportRepositoryInterface $sendReportRepository
     * @param ConnectionDataRepositoryInterface $connectionDataRepository
     * @param CredentialsRepositoryInterface $credentialsRepository
     * @param CountryConfigurationRepositoryInterface $countryConfigurationRepository
     * @param DeploymentsRepositoryInterface $deploymentsRepository
     * @param GeneralSettingsRepositoryInterface $generalSettingsRepository
     * @param SeQuraOrderRepositoryInterface $sequraOrderRepository
     * @param OrderStatusSettingsRepositoryInterface $orderStatusSettingsRepository
     * @param PaymentMethodRepositoryInterface $paymentMethodRepository
     * @param WidgetSettingsRepositoryInterface $widgetSettingsRepository
     * @param StatisticalDataRepositoryInterface $statisticalDataRepository
     * @param TransactionLogRepositoryInterface $transactionLogRepository
     * @param StoreIntegrationService $storeIntegrationService
     */
    public function __construct(
        DisconnectServiceInterface $integrationDisconnectService,
        SendReportRepositoryInterface $sendReportRepository,
        ConnectionDataRepositoryInterface $connectionDataRepository,
        CredentialsRepositoryInterface $credentialsRepository,
        CountryConfigurationRepositoryInterface $countryConfigurationRepository,
        DeploymentsRepositoryInterface $deploymentsRepository,
        GeneralSettingsRepositoryInterface $generalSettingsRepository,
        SeQuraOrderRepositoryInterface $sequraOrderRepository,
        OrderStatusSettingsRepositoryInterface $orderStatusSettingsRepository,
        PaymentMethodRepositoryInterface $paymentMethodRepository,
        WidgetSettingsRepositoryInterface $widgetSettingsRepository,
        StatisticalDataRepositoryInterface $statisticalDataRepository,
        TransactionLogRepositoryInterface $transactionLogRepository,
        StoreIntegrationService $storeIntegrationService
    ) {
        $this->integrationDisconnectService = $integrationDisconnectService;
        $this->sendReportRepository = $sendReportRepository;
        $this->connectionDataRepository = $connectionDataRepository;
        $this->credentialsRepository = $credentialsRepository;
        $this->countryConfigurationRepository = $countryConfigurationRepository;
        $this->deploymentsRepository = $deploymentsRepository;
        $this->generalSettingsRepository = $generalSettingsRepository;
        $this->sequraOrderRepository = $sequraOrderRepository;
        $this->orderStatusSettingsRepository = $orderStatusSettingsRepository;
        $this->paymentMethodRepository = $paymentMethodRepository;
        $this->widgetSettingsRepository = $widgetSettingsRepository;
        $this->statisticalDataRepository = $statisticalDataRepository;
        $this->transactionLogRepository = $transactionLogRepository;
        $this->storeIntegrationService = $storeIntegrationService;
    }

    /**
     * Disconnects integration for deployment, if full disconnect is true delete all data.
     *
     * @param string $deploymentId
     * @param bool $isFullDisconnect
     *
     * @return void
     *
     * @throws PaymentMethodNotFoundException
     */
    public function disconnect(string $deploymentId, bool $isFullDisconnect): void
    {
        $connectionData = $this->connectionDataRepository->getConnectionDataByDeploymentId($deploymentId);
        $this->storeIntegrationService->deleteStoreIntegration($connectionData);
        $this->connectionDataRepository->deleteConnectionDataByDeploymentId($deploymentId);
        if (!$isFullDisconnect) {
            $this->removeAllDeploymentData($deploymentId);

            return;
        }

        $this->credentialsRepository->deleteCredentialsByDeploymentId($deploymentId);
        $this->countryConfigurationRepository->deleteCountryConfigurations();
        $this->deploymentsRepository->deleteDeployments();
        $this->generalSettingsRepository->deleteGeneralSettings();
        $this->sequraOrderRepository->deleteAllOrders();
        $this->orderStatusSettingsRepository->getOrderStatusMapping();
        $this->orderStatusSettingsRepository->deleteOrderStatusMapping();
        $this->paymentMethodRepository->deleteAllPaymentMethods();
        $this->widgetSettingsRepository->deleteWidgetSettings();
        $this->sendReportRepository->deleteSendReportForContext(StoreContext::getInstance()->getStoreId());
        $this->statisticalDataRepository->deleteStatisticalData();
        $this->transactionLogRepository->deleteAllTransactionLogs();

        $this->integrationDisconnectService->disconnect();
    }

    /**
     * Removes all data connected to given deployment
     *
     * @param string $deploymentId
     *
     * @return void
     * @throws PaymentMethodNotFoundException
     */
    private function removeAllDeploymentData(string $deploymentId): void
    {
        // Removes all credentials for given deployment and gets merchant ids connected to that deployment
        $merchantIds = $this->credentialsRepository->deleteCredentialsByDeploymentId($deploymentId);

        // Removes country configurations connected to the deployment
        $countryConfigurations = $this->countryConfigurationRepository->getCountryConfiguration();
        $newCountyConfigurations = [];
        foreach ($countryConfigurations as $countryConfiguration) {
            if (!in_array($countryConfiguration->getMerchantId(), $merchantIds, true)) {
                $newCountyConfigurations[] = $countryConfiguration;
            }
        }

        $this->countryConfigurationRepository->setCountryConfiguration($newCountyConfigurations);

        // Removes all payment methods connected to the deployment
        foreach ($merchantIds as $merchantId) {
            $this->paymentMethodRepository->deletePaymentMethods($merchantId);
        }
    }
}
