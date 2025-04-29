<?php

namespace SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Services;

use Exception;
use SeQura\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Services\CountryConfigurationService;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Exceptions\PaymentMethodNotFoundException;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Services\PaymentMethodsService;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\ValidateAssetsKeyRequest;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\WidgetInitializer;
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
    public const WIDGET_PAYMENT_METHODS = ['i1', 'pp5', 'pp3', 'pp6', 'pp9', 'sp1'];

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
     * @throws PaymentMethodNotFoundException
     */
    public function isAssetsKeyValid(string $assetsKey): bool
    {
        $countryConfig = $this->countryConfigService->getCountryConfiguration();
        $connectionSettings = $this->connectionService->getConnectionData();

        if (empty($countryConfig) || !isset($countryConfig[0]) || $connectionSettings === null) {
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


    /**
     * Returns widget initialize data
     *
     * @param string $shippingCountry
     * @param string $currentCountry
     *
     * @return WidgetInitializer
     *
     * @throws HttpRequestException
     * @throws PaymentMethodNotFoundException
     * @throws Exception
     */
    public function getWidgetInitializeData(string $shippingCountry, string $currentCountry): WidgetInitializer
    {
        $merchantId = $this->getMerchantId($shippingCountry, $currentCountry);

        return new WidgetInitializer(
            $this->getAssetsKey(),
            $merchantId,
            $this->getWidgetSupportedProducts($merchantId),
            $this->getScriptUri()
        );
    }

    /**
     * Returns script uri
     *
     * @return string
     * @throws Exception
     */
    protected function getScriptUri(): string
    {
        $settings = $this->connectionService->getConnectionData();
        if (!$settings || !$settings->getEnvironment()) {
            return '';
        }

        return "https://{$settings->getEnvironment()}.sequracdn.com/assets/sequra-checkout.min.js";
    }

    /**
     * Returns asset key
     *
     * @return string
     * @throws Exception
     */
    protected function getAssetsKey(): string
    {
        $settings = $this->getWidgetSettings();

        return $settings ? $settings->getAssetsKey() : '';
    }

    /**
     * Returns the merchant id for given country
     *
     * @param string $shippingCountry
     * @param string $currentCountry
     *
     * @return string
     */
    protected function getMerchantId(string $shippingCountry, string $currentCountry): string
    {
        $countryConfigurations = $this->countryConfigService->getCountryConfiguration();

        foreach ($countryConfigurations as $country) {
            if ($country->getCountryCode() === $shippingCountry && !empty($country->getMerchantId())) {
                return $country->getMerchantId();
            }
        }

        foreach ($countryConfigurations as $country) {
            if ($country->getCountryCode() === $currentCountry && !empty($country->getMerchantId())) {
                return $country->getMerchantId();
            }
        }

        return '';
    }

    /**
     * Returns available widget products for given merchant id
     *
     * @param string $merchantId
     *
     * @return array<string>
     * @throws HttpRequestException
     * @throws PaymentMethodNotFoundException
     */
    protected function getWidgetSupportedProducts(string $merchantId): array
    {
        $paymentMethods = $this->paymentMethodsService->getMerchantProducts(
            $merchantId
        );
        $widgetSupportedPaymentMethods = [];

        foreach ($paymentMethods as $paymentMethod) {
            if (in_array($paymentMethod, self::WIDGET_PAYMENT_METHODS, true)) {
                $widgetSupportedPaymentMethods [] = $paymentMethod;
            }
        }

        return $widgetSupportedPaymentMethods;
    }
}
