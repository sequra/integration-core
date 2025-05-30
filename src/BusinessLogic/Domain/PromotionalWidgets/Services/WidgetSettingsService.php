<?php

namespace SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Services;

use Exception;
use SeQura\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Services\CountryConfigurationService;
use SeQura\Core\BusinessLogic\Domain\Integration\PromotionalWidgets\WidgetConfiguratorInterface;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Exceptions\PaymentMethodNotFoundException;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Models\SeQuraPaymentMethod;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Services\PaymentMethodsService;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\MiniWidgetMessagesProvider;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\ValidateAssetsKeyRequest;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\Widget;
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
    public const WIDGET_SUPPORTED_CATEGORIES_ON_PRODUCT_PAGE = ['part_payment', 'pay_later'];
    public const WIDGET_SUPPORTED_CATEGORIES_ON_CART_PAGE = ['part_payment', 'pay_later'];
    public const MINI_WIDGET_SUPPORTED_CATEGORIES_ON_PRODUCT_LISTING_PAGE = ['part_payment'];

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
     * @var WidgetConfiguratorInterface
     */
    protected $widgetConfigurator;

    /**
     * @param WidgetSettingsRepositoryInterface $widgetSettingsRepository
     * @param PaymentMethodsService $paymentMethodsService
     * @param CountryConfigurationService $countryConfigService
     * @param ConnectionService $connectionService
     * @param WidgetsProxyInterface $widgetsProxy
     * @param WidgetConfiguratorInterface $widgetConfigurator
     */
    public function __construct(
        WidgetSettingsRepositoryInterface $widgetSettingsRepository,
        PaymentMethodsService $paymentMethodsService,
        CountryConfigurationService $countryConfigService,
        ConnectionService $connectionService,
        WidgetsProxyInterface $widgetsProxy,
        WidgetConfiguratorInterface $widgetConfigurator
    ) {
        $this->widgetSettingsRepository = $widgetSettingsRepository;
        $this->paymentMethodsService = $paymentMethodsService;
        $this->countryConfigService = $countryConfigService;
        $this->connectionService = $connectionService;
        $this->widgetsProxy = $widgetsProxy;
        $this->widgetConfigurator = $widgetConfigurator;
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
            $this->getScriptUri(),
            $this->widgetConfigurator->getLocale() ?? 'es-ES',
            $this->widgetConfigurator->getCurrency() ?? 'EUR',
            $this->widgetConfigurator->getDecimalSeparator() ?? ',',
            $this->widgetConfigurator->getThousandsSeparator() ?? '.'
        );
    }

    /**
     * Returns available widget on cart page
     *
     * @param string $shippingCountry
     * @param string $currentCountry
     *
     * @return Widget|null
     * @throws HttpRequestException
     * @throws PaymentMethodNotFoundException
     * @throws Exception
     */
    public function getAvailableWidgetForCartPage(string $shippingCountry, string $currentCountry): ?Widget
    {
        $widgetSettings = $this->getWidgetSettings();
        if (!$widgetSettings || !$widgetSettings->isEnabled()) {
            return null;
        }

        $widgetSettingsForCart = $widgetSettings->getWidgetSettingsForCart();
        if (!$widgetSettingsForCart) {
            return null;
        }

        $selectedProduct = $widgetSettingsForCart->getWidgetProduct();
        $filteredMethod = $this->findPaymentMethod(
            $shippingCountry,
            $currentCountry,
            $selectedProduct,
            self::WIDGET_SUPPORTED_CATEGORIES_ON_CART_PAGE
        );
        if (!$filteredMethod) {
            return null;
        }

        return new Widget(
            $selectedProduct,
            $filteredMethod->getCampaign() ?? '',
            $widgetSettingsForCart->getPriceSelector(),
            $widgetSettingsForCart->getLocationSelector(),
            $widgetSettings->getWidgetConfig(),
            '0'
        );
    }

    /**
     * Returns available mini-widget on product listing page
     *
     * @param string $shippingCountry
     * @param string $currentCountry
     *
     * @return Widget|null
     * @throws HttpRequestException
     * @throws PaymentMethodNotFoundException
     * @throws Exception
     */
    public function getAvailableMiniWidget(string $shippingCountry, string $currentCountry): ?Widget
    {
        $widgetSettings = $this->getWidgetSettings();
        if (!$widgetSettings || !$widgetSettings->isEnabled()) {
            return null;
        }

        $widgetSettingsForProductListing = $widgetSettings->getWidgetSettingsForListing();
        if (!$widgetSettingsForProductListing) {
            return null;
        }

        $selectedProduct = $widgetSettingsForProductListing->getWidgetProduct();
        $filteredMethod = $this->findPaymentMethod(
            $shippingCountry,
            $currentCountry,
            $selectedProduct,
            self::MINI_WIDGET_SUPPORTED_CATEGORIES_ON_PRODUCT_LISTING_PAGE
        );
        if (!$filteredMethod) {
            return null;
        }

        return new Widget(
            $selectedProduct,
            $filteredMethod->getCampaign() ?? '',
            $widgetSettingsForProductListing->getPriceSelector(),
            $widgetSettingsForProductListing->getLocationSelector(),
            $widgetSettings->getWidgetConfig(),
            '0',
            $filteredMethod->getMinAmount() ?? 0,
            $filteredMethod->getMaxAmount() ?? 0,
            '',
            '',
            $this->getMiniWidgetMessage() ?? '',
            $this->getMiniWidgetBelowLimitMessage() ?? ''
        );
    }


    /**
     * Returns available widgets on product page
     *
     * @param string $shippingCountry
     * @param string $currentCountry
     *
     * @return Widget[]
     * @throws HttpRequestException
     * @throws PaymentMethodNotFoundException
     * @throws Exception
     */
    public function getAvailableWidgetsForProductPage(string $shippingCountry, string $currentCountry): array
    {
        $widgetSettings = $this->getWidgetSettings();
        if (!$widgetSettings || !$widgetSettings->isEnabled()) {
            return [];
        }

        $widgetSettingsForProduct = $widgetSettings->getWidgetSettingsForProduct();
        if (!$widgetSettingsForProduct) {
            return [];
        }

        $customWidgetSettings = $widgetSettingsForProduct->getCustomWidgetsSettings();
        $customSettingsByProduct = [];
        foreach ($customWidgetSettings as $customWidgetSetting) {
            $customSettingsByProduct[$customWidgetSetting->getProduct()] = $customWidgetSetting;
        }

        $supportedPaymentMethods = $this->filterPaymentMethodsSupportedOnProductPage($shippingCountry, $currentCountry);
        $widgets = [];

        foreach ($supportedPaymentMethods as $paymentMethod) {
            $product = $paymentMethod->getProduct();
            $customSetting = $customSettingsByProduct[$product] ?? null;

            if ($customSetting && !$customSetting->isDisplayWidget()) {
                continue;
            }

            $widgets[] = new Widget(
                $product,
                $paymentMethod->getCampaign() ?? '',
                $widgetSettingsForProduct->getPriceSelector(),
                ($customSetting && !empty($customSetting->getCustomLocationSelector())) ?
                    $customSetting->getCustomLocationSelector() : $widgetSettingsForProduct->getLocationSelector(),
                ($customSetting && !empty($customSetting->getCustomWidgetStyle())) ?
                    $customSetting->getCustomWidgetStyle() : $widgetSettings->getWidgetConfig(),
                '0',
                $paymentMethod->getMinAmount() ?? 0,
                $paymentMethod->getMaxAmount() ?? 0,
                $widgetSettingsForProduct->getAltPriceSelector(),
                $widgetSettingsForProduct->getAltPriceTriggerSelector()
            );
        }

        return $widgets;
    }

    /**
     * Returns payment methods that are supported on product page
     *
     * @param string $shippingCountry
     * @param string $currentCountry
     *
     * @return SeQuraPaymentMethod[]
     * @throws HttpRequestException
     * @throws PaymentMethodNotFoundException
     */
    protected function filterPaymentMethodsSupportedOnProductPage(string $shippingCountry, string $currentCountry): array
    {
        $paymentMethods = $this->paymentMethodsService->getCachedPaymentMethods(
            $this->getMerchantId($shippingCountry, $currentCountry)
        );

        return array_filter($paymentMethods, static function ($method) {
            return in_array($method->getCategory(), self::WIDGET_SUPPORTED_CATEGORIES_ON_PRODUCT_PAGE);
        });
    }

    /**
     * Finds selected payment method
     *
     * @param string $shippingCountry
     * @param string $currentCountry
     * @param string $selectedProduct
     * @param string[] $categories
     *
     * @return SeQuraPaymentMethod|null
     * @throws HttpRequestException
     * @throws PaymentMethodNotFoundException
     */
    protected function findPaymentMethod(
        string $shippingCountry,
        string $currentCountry,
        string $selectedProduct,
        array $categories
    ): ?SeQuraPaymentMethod {
        $paymentMethods = $this->paymentMethodsService->getCachedPaymentMethods(
            $this->getMerchantId($shippingCountry, $currentCountry)
        );

        foreach ($paymentMethods as $method) {
            if (
                $method->getProduct() === $selectedProduct &&
                in_array($method->getCategory(), $categories, true)
            ) {
                return $method;
            }
        }

        return null;
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
        $paymentMethods = $this->paymentMethodsService->getCachedPaymentMethods($merchantId);
        $supportedCategories = array_unique(array_merge(
            self::WIDGET_SUPPORTED_CATEGORIES_ON_PRODUCT_PAGE,
            self::WIDGET_SUPPORTED_CATEGORIES_ON_CART_PAGE,
            self::MINI_WIDGET_SUPPORTED_CATEGORIES_ON_PRODUCT_LISTING_PAGE
        ));

        $widgetSupportedProducts = [];

        foreach ($paymentMethods as $paymentMethod) {
            if (in_array($paymentMethod->getCategory(), $supportedCategories, true)) {
                $widgetSupportedProducts [] = $paymentMethod->getProduct();
            }
        }

        return $widgetSupportedProducts;
    }

    /**
     * Returns mini widget message according to current country
     *
     * @return string|null
     */
    public function getMiniWidgetMessage(): ?string
    {
        return MiniWidgetMessagesProvider::MINI_WIDGET_MESSAGE[$this->getCountryCode()] ?? null;
    }

    /**
     * Returns mini widget below limit message according to current country
     *
     * @return string|null
     */
    public function getMiniWidgetBelowLimitMessage(): ?string
    {
        return MiniWidgetMessagesProvider::MINI_WIDGET_BELOW_LIMIT_MESSAGE[$this->getCountryCode()] ?? null;
    }

    /**
     * Returns current country code
     *
     * @return string
     */
    protected function getCountryCode(): string
    {
        $locale = $this->widgetConfigurator->getLocale();

        return substr($locale, strpos($locale, '-') + 1);
    }
}
