<?php

namespace SeQura\Core\BusinessLogic\Domain\Checkout\Services;

use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\BadMerchantIdException;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\WrongCredentialsException;
use SeQura\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Exceptions\FailedToRetrieveSellingCountriesException;
use SeQura\Core\BusinessLogic\Domain\Deployments\Exceptions\DeploymentNotFoundException;
use SeQura\Core\BusinessLogic\Domain\Deployments\Services\DeploymentsService;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Models\GeneralSettings;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Services\GeneralSettingsService;
use SeQura\Core\BusinessLogic\Domain\Integration\Product\ProductServiceInterface;
use SeQura\Core\Infrastructure\Http\Exceptions\HttpRequestException;

/**
 * Class CheckoutService
 *
 * Shared eligibility checks used by all storefront flows (promotional widgets,
 * Express Checkout, etc.) — IP allowance, supported currency, product/category
 * eligibility against GeneralSettings.
 *
 * @package SeQura\Core\BusinessLogic\Domain\Checkout\Services
 */
class CheckoutService
{
    public const SUPPORTED_CURRENCIES = ['EUR'];

    /**
     * @var GeneralSettingsService
     */
    protected $generalSettingsService;
    /**
     * @var ProductServiceInterface
     */
    protected $productService;
    /**
     * @var ConnectionService
     */
    protected $connectionService;
    /**
     * @var DeploymentsService
     */
    protected $deploymentsService;
    /**
     * @var ?GeneralSettings
     */
    public static $generalSettings = null;
    /**
     * @var bool
     */
    public static $generalSettingsFetched = false;

    /**
     * @param GeneralSettingsService $generalSettingsService
     * @param ProductServiceInterface $productService
     * @param ConnectionService $connectionService
     * @param DeploymentsService $deploymentsService
     */
    public function __construct(
        GeneralSettingsService $generalSettingsService,
        ProductServiceInterface $productService,
        ConnectionService $connectionService,
        DeploymentsService $deploymentsService
    ) {
        $this->generalSettingsService = $generalSettingsService;
        $this->productService = $productService;
        $this->connectionService = $connectionService;
        $this->deploymentsService = $deploymentsService;
    }

    /**
     * Resolves the storefront script URL for a given deployment. Returns an empty string when no
     * connection data is configured for that deployment.
     *
     * @param string $deployment
     *
     * @return string
     *
     * @throws DeploymentNotFoundException
     */
    public function getScriptUri(string $deployment): string
    {
        $settings = $this->connectionService->getConnectionDataByDeployment($deployment);
        if (!$settings || !$settings->getEnvironment()) {
            return '';
        }

        $deploymentModel = $this->deploymentsService->getDeploymentById($deployment);

        return $settings->getEnvironment() === 'live' ?
            $deploymentModel->getLiveDeploymentURL()->getAssetsBaseUrl() . 'sequra-checkout.min.js' :
            $deploymentModel->getSandboxDeploymentURL()->getAssetsBaseUrl() . 'sequra-checkout.min.js';
    }

    /**
     * Validates if current IP address on checkout, if set in general settings, is supported.
     *
     * @param string $currentIpAddress
     *
     * @return bool
     *
     * @throws BadMerchantIdException
     * @throws FailedToRetrieveSellingCountriesException
     * @throws HttpRequestException
     * @throws WrongCredentialsException
     */
    public function isIpAddressValid(string $currentIpAddress): bool
    {
        $generalSettings = $this->getGeneralSettings();

        if (!$generalSettings) {
            return true;
        }

        $allowedIPAddresses = $generalSettings->getAllowedIPAddresses() ?? [];

        return !(!empty($allowedIPAddresses) && !\in_array($currentIpAddress, $allowedIPAddresses, true));
    }

    /**
     * Returns true if current currency on checkout is supported.
     *
     * @param string $currentCurrency
     *
     * @return bool
     */
    public function isCurrencySupported(string $currentCurrency): bool
    {
        return \in_array($currentCurrency, self::SUPPORTED_CURRENCIES, true);
    }

    /**
     * Returns true if the product's SKU and category are not excluded in SeQura administration.
     *
     * @param string $productId
     *
     * @return bool
     *
     * @throws BadMerchantIdException
     * @throws FailedToRetrieveSellingCountriesException
     * @throws HttpRequestException
     * @throws WrongCredentialsException
     */
    public function isProductSupported(string $productId): bool
    {
        if (empty($productId)) {
            // Product ID was not provided, skip product validation.
            return true;
        }

        $generalSettings = $this->getGeneralSettings();

        if (!$generalSettings) {
            return true;
        }

        if (empty($generalSettings->getEnabledForServices()) && $this->productService->isProductVirtual($productId)) {
            return false;
        }

        $productSku = $this->productService->getProductsSkuByProductId($productId);

        if ($productSku) {
            $excludedProducts = $generalSettings->getExcludedProducts() ?? [];

            if (
                $excludedProducts &&
                \in_array($productSku, $excludedProducts, true)
            ) {
                return false;
            }
        }

        $excludedCategories = $generalSettings->getExcludedCategories() ?? [];

        if (
            $excludedCategories &&
            !empty(array_intersect(
                $this->productService->getProductCategoriesByProductId($productId),
                $excludedCategories
            ))
        ) {
            return false;
        }

        return true;
    }

    /**
     * Composite eligibility check for the promotional widget flow.
     *
     * Cart-page widgets pass only currency + IP; product-page widgets also pass the product id.
     *
     * @param string $currency
     * @param string $ipAddress
     * @param ?string $productId Pass when checking a specific product (mini-widget / product-page widget).
     *
     * @return bool
     *
     * @throws BadMerchantIdException
     * @throws FailedToRetrieveSellingCountriesException
     * @throws HttpRequestException
     * @throws WrongCredentialsException
     */
    public function isWidgetSupported(string $currency, string $ipAddress, ?string $productId = null): bool
    {
        if (!$this->isCurrencySupported($currency)) {
            return false;
        }

        if (!$this->isIpAddressValid($ipAddress)) {
            return false;
        }

        if ($productId !== null && !$this->isProductSupported($productId)) {
            return false;
        }

        return true;
    }

    /**
     * Composite eligibility check for the Express Checkout flow.
     *
     * @param string $currency
     * @param string $ipAddress
     * @param string[] $productIds Product references in the cart (empty array = no per-product check).
     *
     * @return bool
     *
     * @throws BadMerchantIdException
     * @throws FailedToRetrieveSellingCountriesException
     * @throws HttpRequestException
     * @throws WrongCredentialsException
     */
    public function isExpressCheckoutSupported(string $currency, string $ipAddress, array $productIds = []): bool
    {
        if (!$this->isCurrencySupported($currency)) {
            return false;
        }

        if (!$this->isIpAddressValid($ipAddress)) {
            return false;
        }

        foreach ($productIds as $productId) {
            if (!$this->isProductSupported($productId)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Caches GeneralSettings across multiple calls within the same HTTP request.
     *
     * @return ?GeneralSettings
     *
     * @throws BadMerchantIdException
     * @throws WrongCredentialsException
     * @throws FailedToRetrieveSellingCountriesException
     * @throws HttpRequestException
     */
    private function getGeneralSettings(): ?GeneralSettings
    {
        if (self::$generalSettingsFetched) {
            return self::$generalSettings;
        }

        self::$generalSettings = $this->generalSettingsService->getGeneralSettings();
        self::$generalSettingsFetched = true;

        return self::$generalSettings;
    }
}
