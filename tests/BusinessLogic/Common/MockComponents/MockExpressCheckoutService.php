<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Models\ExpressCheckoutSettings;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Services\ExpressCheckoutService;

/**
 * Class MockExpressCheckoutService.
 *
 * In-memory stand-in for ExpressCheckoutService. Holds settings in memory and returns canned
 * availability values configured via setters.
 *
 * @package SeQura\Core\Tests\BusinessLogic\Common\MockComponents
 */
class MockExpressCheckoutService extends ExpressCheckoutService
{
    /**
     * @var ?ExpressCheckoutSettings
     */
    private $settings = null;

    /**
     * @var bool
     */
    private $availabilityResult = false;

    /**
     * @inheritDoc
     */
    public function getExpressCheckoutSettings(): ?ExpressCheckoutSettings
    {
        return $this->settings;
    }

    /**
     * @inheritDoc
     */
    public function saveExpressCheckoutSettings(ExpressCheckoutSettings $settings): void
    {
        $this->settings = $settings;
    }

    /**
     * @inheritDoc
     */
    public function isExpressCheckoutAvailable(
        string $page,
        string $shippingCountry,
        string $currency,
        string $ipAddress
    ): bool {
        return $this->availabilityResult;
    }

    /**
     * @param bool $available
     *
     * @return void
     */
    public function setAvailability(bool $available): void
    {
        $this->availabilityResult = $available;
    }
}
