<?php

namespace SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Services;

use Exception;
use SeQura\Core\BusinessLogic\Domain\Checkout\Services\CheckoutInitializationService;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\CredentialsNotFoundException;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\Credentials;
use SeQura\Core\BusinessLogic\Domain\Connection\Services\CredentialsService;
use SeQura\Core\BusinessLogic\Domain\Integration\PromotionalWidgets\MiniWidgetMessagesProviderInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\PromotionalWidgets\WidgetConfiguratorInterface;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Exceptions\PaymentMethodNotFoundException;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Models\SeQuraPaymentMethod;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Services\PaymentMethodsService;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Exceptions\DuplicatedWidgetProductException;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Exceptions\EmptyWidgetSelectorParameterException;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Exceptions\InvalidWidgetStylesException;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\CustomWidgetsSettings;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\Widget;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\WidgetInitializer;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\WidgetSelectorSettings;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\WidgetSettings;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\RepositoryContracts\WidgetSettingsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
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
     * @var WidgetConfiguratorInterface
     */
    protected $widgetConfigurator;
    /**
     * @var MiniWidgetMessagesProviderInterface
     */
    protected $miniWidgetMessagesProvider;
    /**
     * @var CheckoutInitializationService
     */
    protected $checkoutInitializationService;

    /**
     * @param WidgetSettingsRepositoryInterface $widgetSettingsRepository
     * @param PaymentMethodsService $paymentMethodsService
     * @param CredentialsService $credentialsService
     * @param WidgetConfiguratorInterface $widgetConfigurator
     * @param MiniWidgetMessagesProviderInterface $miniWidgetMessagesProvider
     * @param CheckoutInitializationService $checkoutInitializationService
     */
    public function __construct(
        WidgetSettingsRepositoryInterface $widgetSettingsRepository,
        PaymentMethodsService $paymentMethodsService,
        CredentialsService $credentialsService,
        WidgetConfiguratorInterface $widgetConfigurator,
        MiniWidgetMessagesProviderInterface $miniWidgetMessagesProvider,
        CheckoutInitializationService $checkoutInitializationService
    ) {
        $this->widgetSettingsRepository = $widgetSettingsRepository;
        $this->paymentMethodsService = $paymentMethodsService;
        $this->credentialsService = $credentialsService;
        $this->widgetConfigurator = $widgetConfigurator;
        $this->miniWidgetMessagesProvider = $miniWidgetMessagesProvider;
        $this->checkoutInitializationService = $checkoutInitializationService;
    }

    /**
     * Retrieves widget settings.
     *
     * @return WidgetSettings
     *
     * @throws Exception
     */
    public function getWidgetSettings(): WidgetSettings
    {
        return $this->widgetSettingsRepository->getWidgetSettings() ?? $this->widgetConfigurator->getDefaultWidgetSettings();
    }

    /**
     * Sets widget settings. The settings are validated first, so that a configuration
     * a widget cannot be displayed with is refused instead of stored: every page the
     * widgets are turned on for needs the selector the price is read from, a payment
     * method may be configured only once, and the styles must be readable.
     *
     * @param WidgetSettings $settings
     *
     * @return void
     *
     * @throws DuplicatedWidgetProductException
     * @throws EmptyWidgetSelectorParameterException
     * @throws InvalidWidgetStylesException
     * @throws Exception
     */
    public function setWidgetSettings(WidgetSettings $settings): void
    {
        $this->validate($settings);

        $this->widgetSettingsRepository->setWidgetSettings($settings);
    }

    /**
     * Refuses widget settings the widgets cannot be displayed with.
     *
     * @param WidgetSettings $settings
     *
     * @return void
     *
     * @throws DuplicatedWidgetProductException
     * @throws EmptyWidgetSelectorParameterException
     * @throws InvalidWidgetStylesException
     */
    protected function validate(WidgetSettings $settings): void
    {
        $productSettings = $settings->getWidgetSettingsForProduct();

        if ($settings->isDisplayOnProductPage()) {
            $this->assertPriceSelector($productSettings, 'productPage');
        }

        if ($settings->isShowInstallmentsInCartPage()) {
            $this->assertPriceSelector($settings->getWidgetSettingsForCart(), 'cartPage');
        }

        if ($settings->isShowInstallmentsInProductListing()) {
            $this->assertPriceSelector($settings->getWidgetSettingsForListing(), 'productListingPage');
        }

        $customWidgetsSettings = $productSettings ? $productSettings->getCustomWidgetsSettings() : [];

        $this->assertDistinctProducts($customWidgetsSettings);
        $this->assertValidStyles($settings->getWidgetConfig());

        foreach ($customWidgetsSettings as $customWidgetSettings) {
            $this->assertValidStyles($customWidgetSettings->getCustomWidgetStyle());
        }
    }

    /**
     * Verifies that the page the widgets are turned on for names the element its price
     * is read from.
     *
     * @param WidgetSelectorSettings|null $selectorSettings
     * @param string $page
     *
     * @return void
     *
     * @throws EmptyWidgetSelectorParameterException
     */
    protected function assertPriceSelector(?WidgetSelectorSettings $selectorSettings, string $page): void
    {
        if ($selectorSettings !== null && trim($selectorSettings->getPriceSelector()) !== '') {
            return;
        }

        throw new EmptyWidgetSelectorParameterException(
            new TranslatableLabel(
                'The price selector is required for the ' . $page . '.',
                'general.errors.widgetSettings.priceSelectorRequired'
            )
        );
    }

    /**
     * Verifies that no payment method carries more than one configuration of its own,
     * as only one of them could ever be applied.
     *
     * @param CustomWidgetsSettings[] $customWidgetsSettings
     *
     * @return void
     *
     * @throws DuplicatedWidgetProductException
     */
    protected function assertDistinctProducts(array $customWidgetsSettings): void
    {
        $seen = [];
        foreach ($customWidgetsSettings as $customWidgetSettings) {
            $product = $customWidgetSettings->getProduct();

            if ($product === '') {
                continue;
            }

            if (isset($seen[$product])) {
                throw new DuplicatedWidgetProductException(
                    new TranslatableLabel(
                        'The payment method ' . $product . ' is configured more than once.',
                        'general.errors.widgetSettings.duplicatedProduct'
                    )
                );
            }

            $seen[$product] = true;
        }
    }

    /**
     * Verifies that the given widget styles can be read back.
     *
     * @param string|null $styles
     *
     * @return void
     *
     * @throws InvalidWidgetStylesException
     */
    protected function assertValidStyles(?string $styles): void
    {
        if ($styles === null || trim($styles) === '' || $this->isValidJson($styles)) {
            return;
        }

        throw new InvalidWidgetStylesException(
            new TranslatableLabel(
                'The widget styles are not valid JSON.',
                'general.errors.widgetSettings.invalidStyles'
            )
        );
    }

    /**
     * Tells whether the given string can be read as JSON.
     *
     * @param string $json
     *
     * @return bool
     */
    protected function isValidJson(string $json): bool
    {
        \json_decode($json);

        return \json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Returns widget initialize data
     *
     * @param string $shippingCountry
     * @param string $currentCountry
     *
     * @return ?WidgetInitializer
     *
     * @throws HttpRequestException
     * @throws PaymentMethodNotFoundException
     * @throws Exception
     */
    public function getWidgetInitializeData(string $shippingCountry, string $currentCountry): ?WidgetInitializer
    {
        try {
            // Shared library bootstrap (script, merchant identity, locale formatting, products)
            // comes from the feature-neutral CheckoutInitializationService; this method only adds
            // the widget-display settings on top.
            $initializationData = $this->checkoutInitializationService->getInitializationData(
                $shippingCountry,
                $currentCountry
            );
            if (!$initializationData) {
                return null;
            }

            $widgetSettings = $this->getWidgetSettings();

            return new WidgetInitializer(
                $initializationData->getAssetKey(),
                $initializationData->getMerchantId(),
                $initializationData->getProducts(),
                $initializationData->getScriptUri(),
                $initializationData->getLocale(),
                $initializationData->getCurrency(),
                $initializationData->getDecimalSeparator(),
                $initializationData->getThousandSeparator(),
                $widgetSettings->isShowInstallmentsInProductListing(),
                $widgetSettings->isDisplayOnProductPage(),
                $widgetSettings->getWidgetConfig()
            );
        } catch (CredentialsNotFoundException $exception) {
            return null;
        }
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
        if (!$widgetSettings->isShowInstallmentsInCartPage()) {
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
        if (!$widgetSettings->isShowInstallmentsInProductListing()) {
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
        if (!$widgetSettings->isDisplayOnProductPage()) {
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
        return $this->credentialsService->getCredentialsByCountry($shippingCountry, $currentCountry);
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
    public function filterPaymentMethodsSupportedOnProductPage(
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
            return \in_array($method->getCategory(), self::WIDGET_SUPPORTED_CATEGORIES_ON_PRODUCT_PAGE);
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
                \in_array($method->getCategory(), $categories, true)
            ) {
                return $method;
            }
        }

        return null;
    }
}
