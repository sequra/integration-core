<?php

namespace SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Services;

use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Models\GeneralSettings;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Services\GeneralSettingsService;
use SeQura\Core\BusinessLogic\Domain\Integration\Product\ProductServiceInterface;

/**
 * Class WidgetValidationService
 *
 * @package SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Services
 */
class WidgetValidationService
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
     */
    public function __construct(
        GeneralSettingsService $generalSettingsService,
        ProductServiceInterface $productService
    ) {
        $this->generalSettingsService = $generalSettingsService;
        $this->productService = $productService;
    }

    /**
     * Validates if current IP address on checkout, if set in general settings, is supported.
     *
     * @param string $currentIpAddress
     *
     * @return bool
     */
    public function isIpAddressValid(string $currentIpAddress): bool
    {
        $generalSettings = $this->getGeneralSettings();

        if (!$generalSettings) {
            return true;
        }

        $allowedIPAddresses = $generalSettings->getAllowedIPAddresses() ?? [];

        return !(!empty($allowedIPAddresses) && !in_array($currentIpAddress, $allowedIPAddresses, true));
    }

    /**
     * Returns true if current currency on checkout is supported for widgets.
     *
     * @param string $currentCurrency
     *
     * @return bool
     */
    public function isCurrencySupported(string $currentCurrency): bool
    {
        return in_array($currentCurrency, self::SUPPORTED_CURRENCIES, true);
    }

    /**
     * Returns true if products sku and category are not excluded in SeQura administration.
     *
     * @param string $productId
     *
     * @return bool
     */
    public function isProductSupported(string $productId): bool
    {
        if(empty($productId)) {
            // Product ID was not provided, skip product validation.
            return true;
        }

        if ($this->productService->isProductVirtual($productId)) {
            return false;
        }

        $generalSettings = $this->getGeneralSettings();

        if (!$generalSettings) {
            return true;
        }

        $productSku = $this->productService->getProductsSkuByProductId($productId);

        if (!$productSku) {
            return false;
        }

        $excludedProducts = $generalSettings->getExcludedProducts() ?? [];

        if (
            $excludedProducts &&
            in_array($productSku, $excludedProducts, true)
        ) {
            return false;
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
     * Since General Settings are required for multiple methods that can
     * be called in same HTTP request cache General Settings.
     *
     * @return ?GeneralSettings
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
