<?php

namespace SeQura\Core\BusinessLogic\SeQuraAPI\Merchant;

use Exception;
use SeQura\Core\BusinessLogic\Domain\Merchant\Models\GetAvailablePaymentMethodsRequest;
use SeQura\Core\BusinessLogic\Domain\Merchant\ProxyContracts\MerchantProxyInterface;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Models\SeQuraPaymentMethod;
use SeQura\Core\BusinessLogic\SeQuraAPI\Authorization\AuthorizedProxy;
use SeQura\Core\BusinessLogic\SeQuraAPI\Merchant\Requests\GetAvailablePaymentMethodsHttpRequest;

/**
 * Class MerchantProxy
 *
 * @package SeQura\Core\BusinessLogic\SeQuraAPI\Merchant
 */
class MerchantProxy extends AuthorizedProxy implements MerchantProxyInterface
{
    /**
     * @inheritDoc
     *
     * @throws Exception
     */
    public function getAvailablePaymentMethods(GetAvailablePaymentMethodsRequest $request): array
    {
        $response = $this->get(new GetAvailablePaymentMethodsHttpRequest($request))->decodeBodyToArray();

        return $this->getListOfPaymentMethods($response);
    }

    /**
     * Gets a list of SeQuraPaymentMethods from the raw response data.
     *
     * @param array $responseData
     *
     * @return SeQuraPaymentMethod[]
     *
     * @throws Exception
     */
    protected function getListOfPaymentMethods(array $responseData): array
    {
        $paymentMethods = [];

        foreach ($responseData['payment_options'] as $option) {
            foreach ($option['methods'] as $method) {
                $paymentMethods[] = SeQuraPaymentMethod::fromArray($method);
            }
        }

        return $paymentMethods;
    }
}
