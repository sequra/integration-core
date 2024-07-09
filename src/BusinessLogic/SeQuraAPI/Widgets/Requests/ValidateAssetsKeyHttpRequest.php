<?php

namespace SeQura\Core\BusinessLogic\SeQuraAPI\Widgets\Requests;

use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\ValidateAssetsKeyRequest;
use SeQura\Core\BusinessLogic\SeQuraAPI\Exceptions\HttpNoAvailablePaymentMethods;
use SeQura\Core\BusinessLogic\SeQuraAPI\HttpRequest;

/**
 * Class ValidateAssetsKeyHttpRequest
 *
 * @package SeQura\Core\BusinessLogic\SeQuraAPI\Widgets\Requests
 */
class ValidateAssetsKeyHttpRequest extends HttpRequest
{
    /**
     * @param ValidateAssetsKeyRequest $request
     *
     * @throws HttpNoAvailablePaymentMethods
     */
    public function __construct(ValidateAssetsKeyRequest $request)
    {
        parent::__construct(
            '/scripts/' . $request->getMerchantId() . '/' . $request->getAssetsKey() . '/'
            . $this->generatePaymentString($request->getPaymentMethodIds())
        );
    }

    /**
     * @param array $paymentMethodIds
     *
     * @return string
     *
     * @throws HttpNoAvailablePaymentMethods
     */
    protected function generatePaymentString(array $paymentMethodIds)
    {
        $i1Key = array_search('i1', $paymentMethodIds);

        if ($i1Key) {
            unset($paymentMethodIds[$i1Key]);
        }

        if (empty($paymentMethodIds)) {
            throw new HttpNoAvailablePaymentMethods();
        }

        return implode('_', $paymentMethodIds) . '_cost.json';
    }
}
