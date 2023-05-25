<?php

namespace SeQura\Core\BusinessLogic\Domain\Merchant\ProxyContracts;

use SeQura\Core\BusinessLogic\Domain\Merchant\Models\GetAvailablePaymentMethodsRequest;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Models\SeQuraPaymentMethod;
use SeQura\Core\Infrastructure\Http\Exceptions\HttpRequestException;

/**
 * Class MerchantProxyInterface
 *
 * @package SeQura\Core\BusinessLogic\Domain\Merchant\ProxyContracts
 */
interface MerchantProxyInterface
{
    /**
     * Get all available payment methods from SeQura for the provided merchant.
     *
     * @param GetAvailablePaymentMethodsRequest $request
     *
     * @throws HttpRequestException
     *
     * @return SeQuraPaymentMethod[]
     */
    public function getAvailablePaymentMethods(GetAvailablePaymentMethodsRequest $request): array;
}
