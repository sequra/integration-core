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
     * @var string|null
     */
    protected $buttonStyle;

    /**
     * @param bool $available
     * @param string[] $availableCountries ISO country codes for which Express Checkout is available.
     * @param string|null $buttonStyle
     */
    public function __construct(bool $available, array $availableCountries, ?string $buttonStyle)
    {
        $this->available = $available;
        $this->availableCountries = $availableCountries;
        $this->buttonStyle = $buttonStyle;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            'available' => $this->available,
            'availableCountries' => $this->availableCountries,
            'buttonStyle' => $this->buttonStyle,
        ];
    }
}
