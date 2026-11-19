<?php

namespace SeQura\Core\BusinessLogic\CheckoutAPI\ExpressCheckout\Requests;

/**
 * Class BaseExpressCheckoutAvailabilityRequest.
 *
 * Shared storefront context for the Express Checkout availability checks. The guest and the
 * known-customer requests differ only by the shipping country, so the common fields live here.
 *
 * @package SeQura\Core\BusinessLogic\CheckoutAPI\ExpressCheckout\Requests
 */
abstract class BaseExpressCheckoutAvailabilityRequest
{
    /**
     * @var string
     */
    protected $page;

    /**
     * @var string
     */
    protected $currency;

    /**
     * @var string
     */
    protected $ipAddress;

    /**
     * @var string[]
     */
    protected $productIds;

    /**
     * @var string[]
     */
    protected $categoryIds;

    /**
     * @param string $page Page identifier (see ExpressCheckoutPage factories).
     * @param string $currency ISO currency code of the cart total.
     * @param string $ipAddress IP address of the storefront customer.
     * @param string[] $productIds Product references in the cart (used for product eligibility).
     * @param string[] $categoryIds Category references in the cart (used for category eligibility).
     */
    public function __construct(
        string $page,
        string $currency,
        string $ipAddress,
        array $productIds = [],
        array $categoryIds = []
    ) {
        $this->page = $page;
        $this->currency = $currency;
        $this->ipAddress = $ipAddress;
        $this->productIds = $productIds;
        $this->categoryIds = $categoryIds;
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

    /**
     * @return string[]
     */
    public function getProductIds(): array
    {
        return $this->productIds;
    }

    /**
     * @return string[]
     */
    public function getCategoryIds(): array
    {
        return $this->categoryIds;
    }
}
