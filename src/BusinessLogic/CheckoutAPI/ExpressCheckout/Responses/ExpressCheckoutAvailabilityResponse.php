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
     * @param bool $available
     */
    public function __construct(bool $available)
    {
        $this->available = $available;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return ['available' => $this->available];
    }
}
