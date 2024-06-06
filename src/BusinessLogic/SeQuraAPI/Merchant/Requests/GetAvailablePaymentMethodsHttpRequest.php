<?php

namespace SeQura\Core\BusinessLogic\SeQuraAPI\Merchant\Requests;

use SeQura\Core\BusinessLogic\Domain\Merchant\Models\GetAvailablePaymentMethodsRequest;
use SeQura\Core\BusinessLogic\SeQuraAPI\HttpRequest;

/**
 * Class GetAvailablePaymentMethodsHttpRequest
 *
 * @package SeQura\Core\BusinessLogic\SeQuraAPI\Merchant\Requests
 */
class GetAvailablePaymentMethodsHttpRequest extends HttpRequest
{
    /**
     * @param GetAvailablePaymentMethodsRequest $request
     */
    public function __construct(GetAvailablePaymentMethodsRequest $request)
    {
        parent::__construct('/merchants/' . $request->getMerchantId() . '/payment_methods');
    }
}
