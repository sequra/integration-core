<?php

namespace SeQura\Core\BusinessLogic\CheckoutAPI\ExpressCheckout\Responses;

use SeQura\Core\BusinessLogic\CheckoutAPI\Solicitation\Response\IdentificationFormResponse;
use SeQura\Core\BusinessLogic\Domain\Order\Models\SeQuraForm;

/**
 * Class ExpressCheckoutUnavailableResponse.
 *
 * Unsuccessful solicit outcome for an expected shopper state (e.g. the cart's shipping
 * country has no configured merchant). Lets integrations distinguish "not available for
 * this shopper" from an actual solicitation failure without an exception being thrown
 * and logged.
 *
 * @package SeQura\Core\BusinessLogic\CheckoutAPI\ExpressCheckout\Responses
 */
class ExpressCheckoutUnavailableResponse extends IdentificationFormResponse
{
    /**
     * @var bool
     */
    protected $successful = false;

    public function __construct()
    {
        parent::__construct(new SeQuraForm(''));
    }
}
