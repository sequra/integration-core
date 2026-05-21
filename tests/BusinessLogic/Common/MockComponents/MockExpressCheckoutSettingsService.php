<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Models\ExpressCheckoutSettings;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Services\ExpressCheckoutSettingsService;

/**
 * Class MockExpressCheckoutSettingsService.
 *
 * @package SeQura\Core\Tests\BusinessLogic\Common\MockComponents
 */
class MockExpressCheckoutSettingsService extends ExpressCheckoutSettingsService
{
    /**
     * @var ?ExpressCheckoutSettings
     */
    private $expressCheckoutSettings = null;

    /**
     * @return ?ExpressCheckoutSettings
     */
    public function getExpressCheckoutSettings(): ?ExpressCheckoutSettings
    {
        return $this->expressCheckoutSettings;
    }

    /**
     * @param ExpressCheckoutSettings $settings
     *
     * @return void
     */
    public function saveExpressCheckoutSettings(ExpressCheckoutSettings $settings): void
    {
        $this->expressCheckoutSettings = $settings;
    }
}
