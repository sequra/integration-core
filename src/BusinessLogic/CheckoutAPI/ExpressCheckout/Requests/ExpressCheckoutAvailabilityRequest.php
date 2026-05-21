<?php

namespace SeQura\Core\BusinessLogic\CheckoutAPI\ExpressCheckout\Requests;

/**
 * Class ExpressCheckoutAvailabilityRequest.
 *
 * Storefront context for the Express Checkout availability check.
 *
 * @package SeQura\Core\BusinessLogic\CheckoutAPI\ExpressCheckout\Requests
 */
class ExpressCheckoutAvailabilityRequest
{
    /**
     * @var string
     */
    protected $page;

    /**
     * @var string
     */
    protected $shippingCountry;

    /**
     * @var string
     */
    protected $currency;

    /**
     * @var string
     */
    protected $ipAddress;

    /**
     * @param string $page Page identifier (see ExpressCheckoutPage factories).
     * @param string $shippingCountry ISO country code of the cart's shipping address.
     * @param string $currency ISO currency code of the cart total.
     * @param string $ipAddress IP address of the storefront customer.
     */
    public function __construct(
        string $page,
        string $shippingCountry,
        string $currency,
        string $ipAddress
    ) {
        $this->page = $page;
        $this->shippingCountry = $shippingCountry;
        $this->currency = $currency;
        $this->ipAddress = $ipAddress;
    }

    /**
     * @return string
     */
    public function getPage(): string
    {
        return $this->page;
    }

    /**
     * @return string
     */
    public function getShippingCountry(): string
    {
        return $this->shippingCountry;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @return string
     */
    public function getIpAddress(): string
    {
        return $this->ipAddress;
    }
}
