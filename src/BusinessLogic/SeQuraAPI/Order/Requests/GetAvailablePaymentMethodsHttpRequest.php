<?php

namespace SeQura\Core\BusinessLogic\SeQuraAPI\Order\Requests;

use SeQura\Core\BusinessLogic\Domain\Order\Models\GetAvailablePaymentMethodsRequest;
use SeQura\Core\BusinessLogic\SeQuraAPI\HttpRequest;

/**
 * Class GetAvailablePaymentMethodsHttpRequest
 *
 * @package SeQura\Core\BusinessLogic\SeQuraAPI\Order\Requests
 */
class GetAvailablePaymentMethodsHttpRequest extends HttpRequest
{
    /**
     * @param GetAvailablePaymentMethodsRequest $request
     */
    public function __construct(GetAvailablePaymentMethodsRequest $request)
    {
        parent::__construct('/orders/' . $request->getOrderId() . '/payment_methods');
    }
}
