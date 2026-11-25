<?php

namespace SeQura\Core\BusinessLogic\CheckoutAPI\ExpressCheckout\Responses;

use SeQura\Core\BusinessLogic\Domain\Order\Models\SeQuraForm;

/**
 * @see \SeQura\Core\BusinessLogic\CheckoutAPI\ExpressCheckout\Controller\ExpressCheckoutController::checkRateLimit()
 *
 * @package SeQura\Core\BusinessLogic\CheckoutAPI\ExpressCheckout\Responses
 */
class ExpressCheckoutThrottledResponse extends ExpressCheckoutSolicitResponse
{
    /**
     * @var bool
     */
    protected $successful = false;

    public function __construct()
    {
        parent::__construct(new SeQuraForm(''), null, null, null);
    }
}
