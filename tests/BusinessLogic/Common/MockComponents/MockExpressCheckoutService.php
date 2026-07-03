<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Models\ExpressCheckoutSettings;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Services\ExpressCheckoutService;
use SeQura\Core\BusinessLogic\Domain\Order\Builders\CreateOrderRequestBuilder;
use SeQura\Core\BusinessLogic\Domain\Order\Models\SeQuraForm;

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
     * @var bool
     */
    private $guestAvailabilityResult = false;

    /**
     * @var ?SeQuraForm
     */
    private $nextFormResult = null;

    /**
     * @var bool
     */
    private $solicitUnavailable = false;

    /**
     * @var ?bool
     */
    private $lastSolicitCheckCountry = null;

    /**
     * @var ?CreateOrderRequestBuilder
     */
    private $lastSolicitBuilder = null;

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
        string $ipAddress,
        array $productIds = [],
        array $categoryIds = []
    ): bool {
        return $this->availabilityResult;
    }

    /**
     * @inheritDoc
     */
    public function isAvailableForGuest(
        string $page,
        string $currency,
        string $ipAddress,
        array $productIds = [],
        array $categoryIds = []
    ): bool {
        return $this->guestAvailabilityResult;
    }

    /**
     * @inheritDoc
     */
    public function solicit(CreateOrderRequestBuilder $builder, bool $checkCountry = false): ?SeQuraForm
    {
        $this->lastSolicitBuilder = $builder;
        $this->lastSolicitCheckCountry = $checkCountry;

        if ($this->solicitUnavailable) {
            return null;
        }

        return $this->nextFormResult ?? new SeQuraForm('');
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

    /**
     * @param bool $available Canned value returned by isAvailableForGuest().
     *
     * @return void
     */
    public function setGuestAvailability(bool $available): void
    {
        $this->guestAvailabilityResult = $available;
    }

    /**
     * Makes subsequent solicit() calls return null (country check rejected the order).
     *
     * @return void
     */
    public function setSolicitUnavailable(): void
    {
        $this->solicitUnavailable = true;
    }

    /**
     * @return ?bool Country-check flag recorded by the most recent solicit() call.
     */
    public function getLastSolicitCheckCountry(): ?bool
    {
        return $this->lastSolicitCheckCountry;
    }

    /**
     * @param SeQuraForm $form Canned form value returned by the next solicit() call.
     *
     * @return void
     */
    public function setNextFormResult(SeQuraForm $form): void
    {
        $this->nextFormResult = $form;
    }

    /**
     * @return ?CreateOrderRequestBuilder Builder recorded by the most recent solicit() call.
     */
    public function getLastSolicitBuilder(): ?CreateOrderRequestBuilder
    {
        return $this->lastSolicitBuilder;
    }
}
