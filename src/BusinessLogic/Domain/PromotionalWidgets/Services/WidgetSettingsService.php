<?php

namespace SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Services;

use Exception;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\Credentials;
use SeQura\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use SeQura\Core\BusinessLogic\Domain\Connection\Services\CredentialsService;
use SeQura\Core\BusinessLogic\Domain\Deployments\Exceptions\DeploymentNotFoundException;
use SeQura\Core\BusinessLogic\Domain\Deployments\Services\DeploymentsService;
use SeQura\Core\BusinessLogic\Domain\Integration\PromotionalWidgets\MiniWidgetMessagesProviderInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\PromotionalWidgets\WidgetConfiguratorInterface;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Exceptions\PaymentMethodNotFoundException;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Models\SeQuraPaymentMethod;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Services\PaymentMethodsService;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\Widget;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\WidgetInitializer;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\WidgetSettings;
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
     * @var CredentialsService
     */
    protected $credentialsService;
    /**
     * @var ConnectionService
     */
    protected $connectionService;
    /**
     * @var WidgetConfiguratorInterface
     */
    protected $widgetConfigurator;
    /**
     * @var MiniWidgetMessagesProviderInterface
     */
    protected $miniWidgetMessagesProvider;
    /**
     * @var DeploymentsService
     */
    protected $deploymentsService;

    /**
     * @param WidgetSettingsRepositoryInterface $widgetSettingsRepository
     * @param PaymentMethodsService $paymentMethodsService
     * @param CredentialsService $credentialsService
     * @param ConnectionService $connectionService
     * @param WidgetConfiguratorInterface $widgetConfigurator
     * @param MiniWidgetMessagesProviderInterface $miniWidgetMessagesProvider
     * @param DeploymentsService $deploymentsService
     */
    public function __construct(
        WidgetSettingsRepositoryInterface $widgetSettingsRepository,
        PaymentMethodsService $paymentMethodsService,
        CredentialsService $credentialsService,
        ConnectionService $connectionService,
        WidgetConfiguratorInterface $widgetConfigurator,
        MiniWidgetMessagesProviderInterface $miniWidgetMessagesProvider,
        DeploymentsService $deploymentsService
    ) {
        $this->widgetSettingsRepository = $widgetSettingsRepository;
        $this->paymentMethodsService = $paymentMethodsService;
        $this->credentialsService = $credentialsService;
        $this->connectionService = $connectionService;
        $this->widgetConfigurator = $widgetConfigurator;
        $this->miniWidgetMessagesProvider = $miniWidgetMessagesProvider;
        $this->deploymentsService = $deploymentsService;
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
     * Returns widget initialize data
     *
     * @param string $shippingCountry
     * @param string $currentCountry
     *
     * @return WidgetInitializer|null
     *
     * @throws HttpRequestException
     * @throws PaymentMethodNotFoundException
     * @throws Exception
     */
    public function getWidgetInitializeData(string $shippingCountry, string $currentCountry): ?WidgetInitializer
    {
        $widgetSettings = $this->getWidgetSettings();
        if (!$widgetSettings || !$widgetSettings->isEnabled()) {
            return null;
        }

        $credentials = $this->getCredentialsByCountry($shippingCountry, $currentCountry);
        $merchantId = $credentials ? $credentials->getMerchantId() : '';

        return new WidgetInitializer(
            $credentials ? $credentials->getAssetsKey() : '',
            $merchantId,
            $this->getWidgetSupportedProducts($merchantId),
            $this->getScriptUri($credentials ? $credentials->getDeployment() : ''),
            $this->widgetConfigurator->getLocale() ?? 'es-ES',
            $this->widgetConfigurator->getCurrency() ?? 'EUR',
            $this->widgetConfigurator->getDecimalSeparator() ?? ',',
            $this->widgetConfigurator->getThousandsSeparator() ?? '.',
            $widgetSettings->isShowInstallmentsInProductListing(),
            $widgetSettings->isDisplayOnProductPage(),
            $widgetSettings->getWidgetConfig()
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
        if (!$widgetSettings || !$widgetSettings->isEnabled() || !$widgetSettings->isShowInstallmentsInCartPage()) {
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
        if (
            !$widgetSettings ||
            !$widgetSettings->isEnabled() ||
            !$widgetSettings->isShowInstallmentsInProductListing()
        ) {
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
            $this->miniWidgetMessagesProvider->getMessage() ?? '',
            $this->miniWidgetMessagesProvider->getBelowLimitMessage() ?? ''
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
        if (!$widgetSettings || !$widgetSettings->isEnabled() || !$widgetSettings->isDisplayOnProductPage()) {
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
     * Returns credentials for given country code
     *
     * @param string $shippingCountry
     * @param string $currentCountry
     *
     * @return Credentials|null
     */
    protected function getCredentialsByCountry(string $shippingCountry, string $currentCountry): ?Credentials
    {
        $credentials = $this->credentialsService->getCredentialsByCountryCode($shippingCountry);
        if (!$credentials) {
            $credentials = $this->credentialsService->getCredentialsByCountryCode($currentCountry);
        }

        return $credentials;
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
    protected function filterPaymentMethodsSupportedOnProductPage(
        string $shippingCountry,
        string $currentCountry
    ): array {
        $credentials = $this->getCredentialsByCountry($shippingCountry, $currentCountry);
        if (!$credentials) {
            return [];
        }

        $paymentMethods = $this->paymentMethodsService->getCachedPaymentMethods(
            $credentials->getMerchantId()
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
        $credentials = $this->getCredentialsByCountry($shippingCountry, $currentCountry);
        if (!$credentials) {
            return null;
        }

        $paymentMethods = $this->paymentMethodsService->getCachedPaymentMethods(
            $credentials->getMerchantId()
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
     * @param string $deployment
     *
     * @return string
     *
     * @throws DeploymentNotFoundException
     */
    protected function getScriptUri(string $deployment): string
    {
        $settings = $this->connectionService->getConnectionDataByDeployment($deployment);
        if (!$settings || !$settings->getEnvironment()) {
            return '';
        }

        $deployment = $this->deploymentsService->getDeploymentById($deployment);

        return $settings->getEnvironment() === 'live' ?
            $deployment->getLiveDeploymentURL()->getAssetsBaseUrl() . 'sequra-checkout.min.js' :
            $deployment->getSandboxDeploymentURL()->getAssetsBaseUrl() . 'sequra-checkout.min.js';
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
}
