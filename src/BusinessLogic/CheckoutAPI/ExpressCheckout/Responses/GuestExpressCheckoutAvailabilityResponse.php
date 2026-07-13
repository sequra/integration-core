<?php

namespace SeQura\Core\BusinessLogic\CheckoutAPI\ExpressCheckout\Responses;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;

/**
 * Class GuestExpressCheckoutAvailabilityResponse.
 *
 * @package SeQura\Core\BusinessLogic\CheckoutAPI\ExpressCheckout\Responses
 */
class GuestExpressCheckoutAvailabilityResponse extends Response
{
    /**
     * @var bool
     */
    protected $available;

    /**
     * @var string[]
     */
    protected $availableCountries;

    /**
     * @param bool $available
     * @param string[] $availableCountries ISO country codes for which Express Checkout is available.
     */
    public function __construct(bool $available, array $availableCountries)
    {
        $this->available = $available;
        $this->availableCountries = $availableCountries;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            'available' => $this->available,
            'availableCountries' => $this->availableCountries,
        ];
    }
}
