<?php

namespace SeQura\Core\BusinessLogic\Domain\PaymentMethod\Services;

use SeQura\Core\BusinessLogic\Domain\Merchant\Models\GetAvailablePaymentMethodsRequest;
use SeQura\Core\BusinessLogic\Domain\Merchant\ProxyContracts\MerchantProxyInterface;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Models\SeQuraPaymentMethod;
use SeQura\Core\Infrastructure\Http\Exceptions\HttpRequestException;

/**
 * Class PaymentMethodService
 *
 * @package SeQura\Core\BusinessLogic\Domain\PaymentMethod\Services
 */
class PaymentMethodsService
{
    /**
     * @var MerchantProxyInterface
     */
    private $merchantProxy;

    /**
     * @param MerchantProxyInterface $merchantProxy
     */
    public function __construct(MerchantProxyInterface $merchantProxy)
    {
        $this->merchantProxy = $merchantProxy;
    }

    /**
     * Gets available payment methods for merchant.
     *
     * @param string $merchantId
     *
     * @return SeQuraPaymentMethod[]
     *
     * @throws HttpRequestException
     */
    public function getMerchantsPaymentMethods(string $merchantId): array
    {
        return $this->merchantProxy->getAvailablePaymentMethods(new GetAvailablePaymentMethodsRequest($merchantId));
    }
}
