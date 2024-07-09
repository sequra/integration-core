<?php

namespace SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Services;

use Exception;
use SeQura\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Services\CountryConfigurationService;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Services\PaymentMethodsService;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\ValidateAssetsKeyRequest;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\WidgetSettings;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\ProxyContracts\WidgetsProxyInterface;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\RepositoryContracts\WidgetSettingsRepositoryInterface;
use SeQura\Core\Infrastructure\Http\Exceptions\HttpRequestException;

/**
 * Class WidgetSettingsService
 *
 * @package SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Services
 */
class WidgetSettingsService
{
    /**
     * @var WidgetSettingsRepositoryInterface
     */
    protected $widgetSettingsRepository;
    /**
     * @var PaymentMethodsService
     */
    protected $paymentMethodsService;
    /**
     * @var CountryConfigurationService
     */
    protected $countryConfigService;
    /**
     * @var ConnectionService
     */
    protected $connectionService;
    /**
     * @var WidgetsProxyInterface
     */
    protected $widgetsProxy;

    /**
     * @param WidgetSettingsRepositoryInterface $widgetSettingsRepository
     * @param PaymentMethodsService $paymentMethodsService
     * @param CountryConfigurationService $countryConfigService
     * @param ConnectionService $connectionService
     * @param WidgetsProxyInterface $widgetsProxy
     */
    public function __construct(
        WidgetSettingsRepositoryInterface $widgetSettingsRepository,
        PaymentMethodsService $paymentMethodsService,
        CountryConfigurationService $countryConfigService,
        ConnectionService $connectionService,
        WidgetsProxyInterface $widgetsProxy
    ) {
        $this->widgetSettingsRepository = $widgetSettingsRepository;
        $this->paymentMethodsService = $paymentMethodsService;
        $this->countryConfigService = $countryConfigService;
        $this->connectionService = $connectionService;
        $this->widgetsProxy = $widgetsProxy;
    }

    /**
     * Retrieves widget settings.
     *
     * @return WidgetSettings|null
     *
     * @throws Exception
     */
    public function getWidgetSettings(): ?WidgetSettings
    {
        return $this->widgetSettingsRepository->getWidgetSettings();
    }

    /**
     * Sets widget settings.
     *
     * @param WidgetSettings $settings
     *
     * @return void
     *
     * @throws Exception
     */
    public function setWidgetSettings(WidgetSettings $settings): void
    {
        $this->widgetSettingsRepository->setWidgetSettings($settings);
    }

    /**
     * Checks if assets key is valid.
     *
     * @param string $assetsKey
     *
     * @return bool
     *
     * @throws HttpRequestException
     */
    public function isAssetsKeyValid(string $assetsKey): bool
    {
        $countryConfig = $this->countryConfigService->getCountryConfiguration();
        $connectionSettings = $this->connectionService->getConnectionData();

        if (empty($countryConfig) || !isset($countryConfig[0]) || empty($connectionSettings)) {
            return false;
        }

        $firstMerchantId = $countryConfig[0]->getMerchantId();
        $paymentMethods = $this->paymentMethodsService->getMerchantProducts(
            $firstMerchantId
        );

        if (empty($paymentMethods)) {
            return false;
        }

        try {
            $this->widgetsProxy->validateAssetsKey(new ValidateAssetsKeyRequest(
                $firstMerchantId,
                $paymentMethods,
                $assetsKey,
                $connectionSettings->getEnvironment()
            ));

            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
