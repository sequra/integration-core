<?php

namespace SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Services;

use SeQura\Core\BusinessLogic\Domain\Checkout\Services\CheckoutService;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\BadMerchantIdException;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\WrongCredentialsException;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Exceptions\FailedToRetrieveSellingCountriesException;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Models\CountryConfiguration;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Services\CountryConfigurationService;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Models\ExpressCheckoutSettings;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\RepositoryContracts\ExpressCheckoutSettingsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Order\Builders\CreateOrderRequestBuilder;
use SeQura\Core\BusinessLogic\Domain\Order\Builders\PrebuiltCreateOrderRequestBuilder;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidUrlException;
use SeQura\Core\BusinessLogic\Domain\Order\Models\SeQuraForm;
use SeQura\Core\BusinessLogic\Domain\Order\Service\OrderService;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionDataNotFoundException;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\CredentialsNotFoundException;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Exceptions\PaymentMethodNotFoundException;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Services\PaymentMethodsService;
use SeQura\Core\Infrastructure\Http\Exceptions\HttpRequestException;

/**
 * Class ExpressCheckoutService
 *
 * Owns Express Checkout settings persistence (get/save, used by the configuration webhooks)
 * and the runtime availability guard chain.
 *
 * Note: this is a domain-layer service. It must not import anything from the adapter layer
 * (CheckoutAPI / AdminAPI / ConfigurationWebhookAPI) — callers pass primitive context fields.
 *
 * @package SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Services
 */
class ExpressCheckoutService
{
    /**
     * @var ExpressCheckoutSettingsRepositoryInterface
     */
    protected $expressCheckoutSettingsRepository;

    /**
     * @var CheckoutService
     */
    protected $checkoutService;

    /**
     * @var CountryConfigurationService
     */
    protected $countryConfigurationService;

    /**
     * @var PaymentMethodsService
     */
    protected $paymentMethodsService;

    /**
     * @var OrderService
     */
    protected $orderService;

    /**
     * @param ExpressCheckoutSettingsRepositoryInterface $expressCheckoutSettingsRepository
     * @param CheckoutService $checkoutService
     * @param CountryConfigurationService $countryConfigurationService
     * @param PaymentMethodsService $paymentMethodsService
     * @param OrderService $orderService
     */
    public function __construct(
        ExpressCheckoutSettingsRepositoryInterface $expressCheckoutSettingsRepository,
        CheckoutService $checkoutService,
        CountryConfigurationService $countryConfigurationService,
        PaymentMethodsService $paymentMethodsService,
        OrderService $orderService
    ) {
        $this->expressCheckoutSettingsRepository = $expressCheckoutSettingsRepository;
        $this->checkoutService = $checkoutService;
        $this->countryConfigurationService = $countryConfigurationService;
        $this->paymentMethodsService = $paymentMethodsService;
        $this->orderService = $orderService;
    }

    /**
     * Retrieves Express Checkout settings from the repository.
     *
     * @return ExpressCheckoutSettings|null
     */
    public function getExpressCheckoutSettings(): ?ExpressCheckoutSettings
    {
        return $this->expressCheckoutSettingsRepository->getExpressCheckoutSettings();
    }

    /**
     * Persists Express Checkout settings via the repository.
     *
     * @param ExpressCheckoutSettings $settings
     *
     * @return void
     */
    public function saveExpressCheckoutSettings(ExpressCheckoutSettings $settings): void
    {
        $this->expressCheckoutSettingsRepository->setExpressCheckoutSettings($settings);
    }

    /**
     * Runs the availability guard chain for a known-customer storefront context.
     *
     * Reuses the guest guards (page enabled + currency/IP/product/category eligibility) and adds
     * the country-specific checks: the shipping country must map to a configured merchant that has
     * at least one cached payment method.
     *
     * @param string $page Page identifier (see ExpressCheckoutPage factories).
     * @param string $shippingCountry ISO country code of the cart's shipping address.
     * @param string $currency ISO currency code of the cart total.
     * @param string $ipAddress IP address of the storefront customer.
     * @param string[] $productIds Product references in the cart (used for product eligibility).
     * @param string[] $categoryIds Category references in the cart (used for category eligibility).
     *
     * @return bool
     *
     * @throws BadMerchantIdException
     * @throws WrongCredentialsException
     * @throws FailedToRetrieveSellingCountriesException
     * @throws HttpRequestException
     */
    public function isExpressCheckoutAvailable(
        string $page,
        string $shippingCountry,
        string $currency,
        string $ipAddress,
        array $productIds = [],
        array $categoryIds = []
    ): bool {
        if (!$this->isAvailableForGuest($page, $currency, $ipAddress, $productIds, $categoryIds)) {
            return false;
        }

        $merchantId = $this->resolveMerchantId($shippingCountry);
        if ($merchantId === null) {
            return false;
        }

        try {
            return !empty($this->paymentMethodsService->getCachedPaymentMethods($merchantId));
        } catch (PaymentMethodNotFoundException $exception) {
            // No payment methods configured for the merchant: Express Checkout is simply unavailable.
            return false;
        }
    }

    /**
     * Runs the availability guard chain for a GUEST storefront context, where the
     * shipping/billing country is not yet known. The country-specific guards are skipped; every
     * other condition is identical to the known-customer check.
     *
     * @param string $page Page identifier (see ExpressCheckoutPage factories).
     * @param string $currency ISO currency code of the cart total.
     * @param string $ipAddress IP address of the storefront customer.
     * @param string[] $productIds Product references in the cart (used for product eligibility).
     * @param string[] $categoryIds Category references in the cart (used for category eligibility).
     *
     * @return bool
     *
     * @throws BadMerchantIdException
     * @throws WrongCredentialsException
     * @throws FailedToRetrieveSellingCountriesException
     * @throws HttpRequestException
     */
    public function isAvailableForGuest(
        string $page,
        string $currency,
        string $ipAddress,
        array $productIds = [],
        array $categoryIds = []
    ): bool {
        $settings = $this->getExpressCheckoutSettings();
        if ($settings === null || !$settings->isPageEnabled($page)) {
            return false;
        }

        return $this->checkoutService->isExpressCheckoutSupported($currency, $ipAddress, $productIds, $categoryIds);
    }

    /**
     * Solicits the order and returns the identification form for the Express Checkout flow.
     *
     * @param CreateOrderRequestBuilder $builder
     * @param bool $checkCountry When true, the order's delivery country is validated against the
     * configured countries first. An unsupported country is an expected shopper state, not an
     * error, so it returns null instead of the solicit failing on the missing merchant.
     *
     * @return SeQuraForm|null Null when the country check is enabled and the delivery country has
     * no configured merchant.
     *
     * @throws HttpRequestException
     * @throws ConnectionDataNotFoundException
     * @throws CredentialsNotFoundException
     * @throws InvalidUrlException
     * @throws FailedToRetrieveSellingCountriesException
     */
    public function solicit(CreateOrderRequestBuilder $builder, bool $checkCountry = false): ?SeQuraForm
    {
        $createOrderRequest = $builder->build();

        if (
            $checkCountry &&
            $this->resolveMerchantId($createOrderRequest->getDeliveryAddress()->getCountryCode()) === null
        ) {
            return null;
        }

        return $this->orderService->solicitExpressCheckoutForm(
            new PrebuiltCreateOrderRequestBuilder($createOrderRequest)
        );
    }

    /**
     * @param string $countryCode
     *
     * @return ?string
     *
     * @throws FailedToRetrieveSellingCountriesException
     */
    private function resolveMerchantId(string $countryCode): ?string
    {
        $configurations = $this->countryConfigurationService->getCountryConfiguration() ?? [];
        foreach ($configurations as $configuration) {
            /**
             * @var CountryConfiguration $configuration
             */
            if ($configuration->getCountryCode() === $countryCode) {
                return $configuration->getMerchantId();
            }
        }

        return null;
    }
}
