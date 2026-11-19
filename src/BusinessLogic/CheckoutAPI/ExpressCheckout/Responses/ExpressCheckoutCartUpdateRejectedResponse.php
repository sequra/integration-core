<?php

namespace SeQura\Core\BusinessLogic\CheckoutAPI\ExpressCheckout\Responses;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;

/**
 * @package SeQura\Core\BusinessLogic\CheckoutAPI\ExpressCheckout\Responses
 */
class ExpressCheckoutCartUpdateRejectedResponse extends Response
{
    public const REASON_INVALID_REQUEST = 'invalid_request';
    public const REASON_CART_UNAVAILABLE = 'cart_unavailable';
    public const REASON_NOT_ELIGIBLE = 'not_eligible';

    /**
     * @var bool
     */
    protected $successful = false;

    /**
     * @var string
     */
    protected $reason;

    /**
     * @param string $reason One of the REASON_* constants.
     */
    public function __construct(string $reason)
    {
        $this->reason = $reason;
    }

    /**
     * @return string
     */
    public function getReason(): string
    {
        return $this->reason;
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return ['reason' => $this->reason];
    }
}
