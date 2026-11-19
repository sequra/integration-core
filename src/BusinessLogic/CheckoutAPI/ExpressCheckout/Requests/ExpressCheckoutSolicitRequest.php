<?php

namespace SeQura\Core\BusinessLogic\CheckoutAPI\ExpressCheckout\Requests;

use SeQura\Core\BusinessLogic\Domain\Order\Builders\CreateOrderRequestBuilder;

/**
 * Class ExpressCheckoutSolicitRequest.
 *
 * Carries the platform-supplied CreateOrderRequestBuilder for the button-click flow.
 * Callers are expected to have passed the `isAvailable` gate already; the controller
 * does not re-run the availability guards. Callers must skip the request entirely
 * for guest sessions (same expectation as ExpressCheckoutAvailabilityRequest).
 *
 * @package SeQura\Core\BusinessLogic\CheckoutAPI\ExpressCheckout\Requests
 */
class ExpressCheckoutSolicitRequest
{
    /**
     * @var CreateOrderRequestBuilder
     */
    protected $builder;

    /**
     * @var bool
     */
    protected $checkCountry;

    /**
     * @param CreateOrderRequestBuilder $builder Platform-supplied builder used to solicit the order.
     * @param bool $checkCountry When true, the service validates that the order's delivery country
     * has a configured merchant before soliciting; an unsupported country yields an unsuccessful
     * response instead of the solicit failing on the missing merchant.
     */
    public function __construct(CreateOrderRequestBuilder $builder, bool $checkCountry = false)
    {
        $this->builder = $builder;
        $this->checkCountry = $checkCountry;
    }

    /**
     * @return CreateOrderRequestBuilder
     */
    public function getBuilder(): CreateOrderRequestBuilder
    {
        return $this->builder;
    }

    /**
     * @return bool
     */
    public function isCountryCheckEnabled(): bool
    {
        return $this->checkCountry;
    }
}
