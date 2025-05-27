<?php

namespace SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Services;

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

    public function __construct(
        GeneralSettingsService $generalSettingsService
    ) {
        $this->generalSettingsService = $generalSettingsService;
    }

    /**
     * Validates if current currency and current IP address are supported
     *
     * @param string $currentCurrency
     * @param string $currentIpAddress
     *
     * @return bool
     */
    public function validateCurrentCurrencyAndIpAddress(string $currentCurrency, string $currentIpAddress): bool
    {
        if (!in_array($currentCurrency, self::SUPPORTED_CURRENCIES, true)) {
            return false;
        }

        $generalSettings = $this->generalSettingsService->getGeneralSettings();
        if (!$generalSettings) {
            return true;
        }

        $allowedIPAddresses = $generalSettings->getAllowedIPAddresses() ?? [];

        return !(!empty($allowedIPAddresses) && !in_array($currentIpAddress, $allowedIPAddresses, true));
    }
}
