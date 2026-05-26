<?php

namespace SeQura\Core\BusinessLogic\Domain\Disconnect\Services;

use SeQura\Core\BusinessLogic\Domain\AdvancedSettings\RepositoryContracts\AdvancedSettingsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Services\BannerSettingsService;
use SeQura\Core\BusinessLogic\Domain\Connection\RepositoryContracts\ConnectionDataRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Connection\RepositoryContracts\CredentialsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\RepositoryContracts\CountryConfigurationRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Deployments\RepositoryContracts\DeploymentsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\RepositoryContracts\ExpressCheckoutSettingsRepositoryInterface;
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
use SeQura\Core\Infrastructure\Logger\LogContextData;
use SeQura\Core\Infrastructure\Logger\Logger;
use Throwable;

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
     * @var AdvancedSettingsRepositoryInterface $advancedSettingsRepository
     */
    protected $advancedSettingsRepository;

    /**
     * @var BannerSettingsService $bannerSettingsService
     */
    protected $bannerSettingsService;

    /**
     * @var ExpressCheckoutSettingsRepositoryInterface $expressCheckoutSettingsRepository
     */
    protected $expressCheckoutSettingsRepository;

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
     * @param AdvancedSettingsRepositoryInterface $advancedSettingsRepository
     * @param BannerSettingsService $bannerSettingsService
     * @param ExpressCheckoutSettingsRepositoryInterface $expressCheckoutSettingsRepository
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
        StoreIntegrationService $storeIntegrationService,
        AdvancedSettingsRepositoryInterface $advancedSettingsRepository,
        ExpressCheckoutSettingsRepositoryInterface $expressCheckoutSettingsRepository
        AdvancedSettingsRepositoryInterface $advancedSettingsRepository,
        BannerSettingsService $bannerSettingsService
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
        $this->advancedSettingsRepository = $advancedSettingsRepository;
        $this->expressCheckoutSettingsRepository = $expressCheckoutSettingsRepository;
        $this->bannerSettingsService = $bannerSettingsService;
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
        $connectionData = $this->connectionDataRepository
            ->getConnectionDataByDeploymentId($deploymentId);

        if ($connectionData !== null) {
            try {
                $this->storeIntegrationService->deleteStoreIntegration($connectionData);
            } catch (Throwable $e) {
                Logger::logWarning(
                    'Remote store integration deregistration failed during disconnect; continuing with local cleanup.',
                    'Core',
                    [
                        new LogContextData('deploymentId', $deploymentId),
                        new LogContextData('environment', $connectionData->getEnvironment()),
                        new LogContextData('merchantId', $connectionData->getMerchantId()),
                        new LogContextData('message', $e->getMessage()),
                        new LogContextData('code', $e->getCode()),
                        new LogContextData('file', $e->getFile()),
                        new LogContextData('line', $e->getLine()),
                        new LogContextData('trace', $e->getTraceAsString()),
                    ]
                );
            }
            $this->connectionDataRepository->deleteConnectionDataByDeploymentId($deploymentId);
        }

        if (!$isFullDisconnect) {
            $this->removeAllDeploymentData($deploymentId);
            return;
        }

        $this->credentialsRepository->deleteCredentialsByDeploymentId($deploymentId);
        $this->countryConfigurationRepository->deleteCountryConfigurations();
        $this->deploymentsRepository->deleteDeployments();
        $this->generalSettingsRepository->deleteGeneralSettings();
        $this->sequraOrderRepository->deleteAllOrders();
        $this->orderStatusSettingsRepository->deleteOrderStatusMapping();
        $this->paymentMethodRepository->deleteAllPaymentMethods();
        $this->widgetSettingsRepository->deleteWidgetSettings();
        $this->bannerSettingsService->clearBannerSettings();
        $this->sendReportRepository->deleteSendReportForContext(StoreContext::getInstance()->getStoreId());
        $this->statisticalDataRepository->deleteStatisticalData();
        $this->transactionLogRepository->deleteAllTransactionLogs();
        $this->advancedSettingsRepository->deleteAdvancedSettings();
        $this->expressCheckoutSettingsRepository->deleteExpressCheckoutSettings();

        $this->integrationDisconnectService->disconnect();
    }

    /**
     * Removes deployment-scoped data only (credentials, country configs by merchantId,
     * payment methods by merchantId). Store-scoped settings — general, widget, order
     * status, advanced, statistical data, transaction logs, and banner settings/images
     * — are intentionally untouched; they are wiped only on full disconnect.
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
            if (!\in_array($countryConfiguration->getMerchantId(), $merchantIds, true)) {
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
