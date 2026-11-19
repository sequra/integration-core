<?php

namespace SeQura\Core\BusinessLogic\CheckoutAPI\ExpressCheckout\Requests;

/**
 * @package SeQura\Core\BusinessLogic\CheckoutAPI\ExpressCheckout\Requests
 */
class ExpressCheckoutCartUpdateRequest
{
    /**
     * @var mixed
     */
    protected $flowToken;

    /**
     * @var mixed
     */
    protected $payload;

    /**
     * @param mixed $flowToken The raw posted flow_token value.
     * @param mixed $payload The raw posted payload value.
     */
    public function __construct($flowToken, $payload)
    {
        $this->flowToken = $flowToken;
        $this->payload = $payload;
    }

    /**
     * @return mixed
     */
    public function getFlowToken()
    {
        return $this->flowToken;
    }

    /**
     * @return mixed
     */
    public function getPayload()
    {
        return $this->payload;
    }
}
