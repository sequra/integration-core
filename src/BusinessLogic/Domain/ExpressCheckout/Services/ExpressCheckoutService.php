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
     * @param ExpressCheckoutSettingsRepositoryInterface $expressCheckoutSettingsRepository
     * @param CheckoutService $checkoutService
     * @param CountryConfigurationService $countryConfigurationService
     * @param PaymentMethodsService $paymentMethodsService
     */
    public function __construct(
        ExpressCheckoutSettingsRepositoryInterface $expressCheckoutSettingsRepository,
        CheckoutService $checkoutService,
        CountryConfigurationService $countryConfigurationService,
        PaymentMethodsService $paymentMethodsService
    ) {
        $this->expressCheckoutSettingsRepository = $expressCheckoutSettingsRepository;
        $this->checkoutService = $checkoutService;
        $this->countryConfigurationService = $countryConfigurationService;
        $this->paymentMethodsService = $paymentMethodsService;
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
     * Runs the availability guard chain for the given storefront context.
     *
     * @param string $page Page identifier (see ExpressCheckoutPage factories).
     * @param string $shippingCountry ISO country code of the cart's shipping address.
     * @param string $currency ISO currency code of the cart total.
     * @param string $ipAddress IP address of the storefront customer.
     *
     * @return bool
     *
     * @throws BadMerchantIdException
     * @throws WrongCredentialsException
     * @throws FailedToRetrieveSellingCountriesException
     * @throws PaymentMethodNotFoundException
     * @throws HttpRequestException
     */
    public function isExpressCheckoutAvailable(
        string $page,
        string $shippingCountry,
        string $currency,
        string $ipAddress
    ): bool {
        $settings = $this->getExpressCheckoutSettings();
        if ($settings === null || !$settings->isPageEnabled($page)) {
            return false;
        }

        $merchantId = $this->resolveMerchantId($shippingCountry);
        if ($merchantId === null) {
            return false;
        }

        if (!$this->checkoutService->isExpressCheckoutSupported($currency, $ipAddress)) {
            return false;
        }

        if (empty($this->paymentMethodsService->getCachedPaymentMethods($merchantId))) {
            return false;
        }

        return true;
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
