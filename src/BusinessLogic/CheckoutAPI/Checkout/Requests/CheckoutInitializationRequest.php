<?php

namespace SeQura\Core\BusinessLogic\CheckoutAPI\Checkout\Requests;

/**
 * Class CheckoutInitializationRequest.
 *
 * @package SeQura\Core\BusinessLogic\CheckoutAPI\Checkout\Requests
 */
class CheckoutInitializationRequest
{
    /**
     * @var string
     */
    protected $shippingCountry;
    /**
     * @var string
     */
    protected $currentCountry;

    /**
     * @param string $shippingCountry
     * @param string $currentCountry
     */
    public function __construct(string $shippingCountry, string $currentCountry)
    {
        $this->shippingCountry = $shippingCountry;
        $this->currentCountry = $currentCountry;
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
    public function getCurrentCountry(): string
    {
        return $this->currentCountry;
    }
}
