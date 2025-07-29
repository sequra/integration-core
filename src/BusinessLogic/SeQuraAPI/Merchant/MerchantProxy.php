<?php

namespace SeQura\Core\BusinessLogic\SeQuraAPI\Merchant;

use Exception;
use SeQura\Core\BusinessLogic\AdminAPI\PaymentMethods\Requests\GetAvailablePaymentMethodsRequest;
use SeQura\Core\BusinessLogic\Domain\Merchant\ProxyContracts\MerchantProxyInterface;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Models\SeQuraPaymentMethod;
use SeQura\Core\BusinessLogic\SeQuraAPI\Factories\AuthorizedProxyFactory;
use SeQura\Core\BusinessLogic\SeQuraAPI\Merchant\Requests\GetAvailablePaymentMethodsHttpRequest;

/**
 * Class MerchantProxy
 *
 * @package SeQura\Core\BusinessLogic\SeQuraAPI\Merchant
 */
class MerchantProxy implements MerchantProxyInterface
{
    /**
     * @var AuthorizedProxyFactory $authorizedProxyFactory
     */
    private $authorizedProxyFactory;

    /**
     * @param AuthorizedProxyFactory $authorizedProxyFactory
     */
    public function __construct(AuthorizedProxyFactory $authorizedProxyFactory)
    {
        $this->authorizedProxyFactory = $authorizedProxyFactory;
    }

    /**
     * @inheritDoc
     *
     * @throws Exception
     */
    public function getAvailablePaymentMethods(GetAvailablePaymentMethodsRequest $request): array
    {
        $response = $this->authorizedProxyFactory->build($request->getMerchantId())
            ->get(new GetAvailablePaymentMethodsHttpRequest($request))->decodeBodyToArray();

        return $this->getListOfPaymentMethods($response);
    }

    /**
     * Gets a list of SeQuraPaymentMethods from the raw response data.
     *
     * @param mixed[] $responseData
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
                $method['category'] = $option['category'] ?? '';
                $paymentMethods[] = SeQuraPaymentMethod::fromArray($method);
            }
        }

        return $paymentMethods;
    }
}
