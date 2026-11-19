<?php

namespace SeQura\Core\BusinessLogic\CheckoutAPI\ExpressCheckout\Responses;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;

/**
 * Class ExpressCheckoutAvailabilityResponse.
 *
 * @package SeQura\Core\BusinessLogic\CheckoutAPI\ExpressCheckout\Responses
 */
class ExpressCheckoutAvailabilityResponse extends Response
{
    /**
     * @var bool
     */
    protected $available;

    /**
     * @var string|null
     */
    protected $buttonStyle;

    /**
     * @param bool $available
     * @param string|null $buttonStyle
     */
    public function __construct(bool $available, ?string $buttonStyle = null)
    {
        $this->available = $available;
        $this->buttonStyle = $buttonStyle;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            'available' => $this->available,
            'buttonStyle' => $this->buttonStyle,
        ];
    }
}
