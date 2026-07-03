<?php

namespace SeQura\Core\BusinessLogic\CheckoutAPI\ExpressCheckout\Requests;

/**
 * Class GuestExpressCheckoutAvailabilityRequest.
 *
 * Storefront context for the guest Express Checkout availability check. The shipping country is
 * intentionally absent because it is not yet known for guest sessions, so the request carries only
 * the shared fields from the base.
 *
 * @package SeQura\Core\BusinessLogic\CheckoutAPI\ExpressCheckout\Requests
 */
class GuestExpressCheckoutAvailabilityRequest extends BaseExpressCheckoutAvailabilityRequest
{
}
