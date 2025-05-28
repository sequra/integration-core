<?php

namespace SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Services;

use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Models\GeneralSettings;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Services\GeneralSettingsService;

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
     * @param GeneralSettingsService $generalSettingsService
     */
    public function __construct(
        GeneralSettingsService $generalSettingsService
    ) {
        $this->generalSettingsService = $generalSettingsService;
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
        $generalSettings = $this->generalSettingsService->getGeneralSettings();

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
     * @param string $sku
     * @param string[] $categories
     * @param bool $isVirtual
     *
     * @return bool
     */
    public function isProductSupported(string $sku, array $categories, bool $isVirtual = false): bool
    {
        if ($isVirtual) {
            return false;
        }

        $generalSettings = $this->generalSettingsService->getGeneralSettings();

        if (!$generalSettings) {
            return true;
        }

        $excludedProducts = $generalSettings->getExcludedProducts() ?? [];
        if ($excludedProducts && in_array($sku, $excludedProducts, true)) {
            return false;
        }

        $excludedCategories = $generalSettings->getExcludedCategories() ?? [];

        if ($excludedCategories && !empty(array_intersect($categories, $excludedCategories))) {
            return false;
        }

        return true;
    }
}
