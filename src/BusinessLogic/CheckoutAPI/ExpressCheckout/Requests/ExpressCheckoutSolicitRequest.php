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
     * @param CreateOrderRequestBuilder $builder Platform-supplied builder used to solicit the order.
     */
    public function __construct(CreateOrderRequestBuilder $builder)
    {
        $this->builder = $builder;
    }

    /**
     * @return CreateOrderRequestBuilder
     */
    public function getBuilder(): CreateOrderRequestBuilder
    {
        return $this->builder;
    }
}
