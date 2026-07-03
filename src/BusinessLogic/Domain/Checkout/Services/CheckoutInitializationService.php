<?php

namespace SeQura\Core\BusinessLogic\Domain\Checkout\Services;

use SeQura\Core\BusinessLogic\Domain\Checkout\Models\CheckoutInitializationData;
use SeQura\Core\BusinessLogic\Domain\Connection\Services\CredentialsService;
use SeQura\Core\BusinessLogic\Domain\Deployments\Exceptions\DeploymentNotFoundException;
use SeQura\Core\BusinessLogic\Domain\Integration\PromotionalWidgets\WidgetConfiguratorInterface;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Exceptions\PaymentMethodNotFoundException;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Services\PaymentMethodsService;
use SeQura\Core\Infrastructure\Http\Exceptions\HttpRequestException;

/**
 * Class CheckoutInitializationService
 *
 * Builds the feature-neutral storefront bootstrap config for the seQura checkout
 * library (script URL + merchant identity + locale formatting + supported
 * products). Shared by promotional widgets and Express Checkout so neither
 * feature depends on the other to load the library.
 *
 * @package SeQura\Core\BusinessLogic\Domain\Checkout\Services
 */
class CheckoutInitializationService
{
    /**
     * @var CredentialsService
     */
    protected $credentialsService;
    /**
     * @var CheckoutService
     */
    protected $checkoutService;
    /**
     * @var WidgetConfiguratorInterface
     */
    protected $widgetConfigurator;
    /**
     * @var PaymentMethodsService
     */
    protected $paymentMethodsService;

    /**
     * @param CredentialsService $credentialsService
     * @param CheckoutService $checkoutService
     * @param WidgetConfiguratorInterface $widgetConfigurator
     * @param PaymentMethodsService $paymentMethodsService
     */
    public function __construct(
        CredentialsService $credentialsService,
        CheckoutService $checkoutService,
        WidgetConfiguratorInterface $widgetConfigurator,
        PaymentMethodsService $paymentMethodsService
    ) {
        $this->credentialsService = $credentialsService;
        $this->checkoutService = $checkoutService;
        $this->widgetConfigurator = $widgetConfigurator;
        $this->paymentMethodsService = $paymentMethodsService;
    }

    /**
     * Returns the storefront bootstrap config for the seQura checkout library. When no credentials
     * are configured for the shopper's country, the merchant identity is left empty (seQura is not
     * active for that country) but the library bootstrap (script URL, locale formatting) is still
     * returned.
     *
     * @param string $shippingCountry
     * @param string $currentCountry
     *
     * @return CheckoutInitializationData|null
     *
     * @throws HttpRequestException
     * @throws PaymentMethodNotFoundException
     * @throws DeploymentNotFoundException
     */
    public function getInitializationData(string $shippingCountry, string $currentCountry): ?CheckoutInitializationData
    {
        $credentials = $this->credentialsService->getCredentialsByCountry($shippingCountry, $currentCountry);
        $merchantId = $credentials ? $credentials->getMerchantId() : '';

        return new CheckoutInitializationData(
            $credentials ? $credentials->getAssetsKey() : '',
            $merchantId,
            $merchantId !== '' ? $this->paymentMethodsService->getMerchantPromotionalProducts($merchantId) : [],
            $this->checkoutService->getScriptUri($credentials ? $credentials->getDeployment() : ''),
            $this->widgetConfigurator->getLocale() ?? 'es-ES',
            $this->widgetConfigurator->getCurrency() ?? 'EUR',
            $this->widgetConfigurator->getDecimalSeparator() ?? ',',
            $this->widgetConfigurator->getThousandsSeparator() ?? '.'
        );
    }
}
