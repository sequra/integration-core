<?php

namespace SeQura\Core\BusinessLogic\CheckoutAPI\ExpressCheckout\Requests;

/**
 * Class ExpressCheckoutAvailabilityRequest.
 *
 * Storefront context for the known-customer Express Checkout availability check. Identical to the
 * guest request plus the cart's shipping country, which drives the country-specific guards.
 *
 * @package SeQura\Core\BusinessLogic\CheckoutAPI\ExpressCheckout\Requests
 */
class ExpressCheckoutAvailabilityRequest extends BaseExpressCheckoutAvailabilityRequest
{
    /**
     * @var string
     */
    protected $country;

    /**
     * @param string $page Page identifier (see ExpressCheckoutPage factories).
     * @param string $currency ISO currency code of the cart total.
     * @param string $ipAddress IP address of the storefront customer.
     * @param string $country ISO country code of the cart's shipping address.
     * @param string[] $productIds Product references in the cart (used for product eligibility).
     * @param string[] $categoryIds Category references in the cart (used for category eligibility).
     */
    public function __construct(
        string $page,
        string $currency,
        string $ipAddress,
        string $country,
        array $productIds = [],
        array $categoryIds = []
    ) {
        parent::__construct($page, $currency, $ipAddress, $productIds, $categoryIds);

        $this->country = $country;
    }

    /**
     * @return string
     */
    public function getCountry(): string
    {
        return $this->country;
    }
}
