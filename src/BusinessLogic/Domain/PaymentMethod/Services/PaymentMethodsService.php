<?php

namespace SeQura\Core\BusinessLogic\Domain\PaymentMethod\Services;

use SeQura\Core\BusinessLogic\Domain\Merchant\Models\GetAvailablePaymentMethodsRequest;
use SeQura\Core\BusinessLogic\Domain\Merchant\ProxyContracts\MerchantProxyInterface;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Exceptions\PaymentMethodNotFoundException;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Models\SeQuraPaymentMethod;
use SeQura\Core\Infrastructure\Http\Exceptions\HttpRequestException;
use SeQura\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;

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
    protected $merchantProxy;
    /**
     * @var CachedPaymentMethodsService
     */
    protected $cachedPaymentMethodsService;

    /**
     * @param MerchantProxyInterface $merchantProxy
     * @param CachedPaymentMethodsService $cachedPaymentMethodsService
     */
    public function __construct(MerchantProxyInterface $merchantProxy, CachedPaymentMethodsService $cachedPaymentMethodsService)
    {
        $this->merchantProxy = $merchantProxy;
        $this->cachedPaymentMethodsService = $cachedPaymentMethodsService;
    }

    /**
     * Gets available payment methods for merchant.
     *
     * @param  string $merchantId
     * @param  bool $cache
     *
     * @return SeQuraPaymentMethod[]
     *
     * @throws HttpRequestException
     * @throws QueryFilterInvalidParamException
     * @throws PaymentMethodNotFoundException
     */
    public function getMerchantsPaymentMethods(string $merchantId, bool $cache = false): array
    {
        $availablePaymentMethods = $this->merchantProxy->getAvailablePaymentMethods(new GetAvailablePaymentMethodsRequest($merchantId));

        if ($cache) {
            $this->cachedPaymentMethodsService->cachePaymentMethods($availablePaymentMethods);
        }

        return $availablePaymentMethods;
    }

    /**
     * Gets available products for the merchant.
     *
     * @param string $merchantId
     *
     * @return string[]
     *
     * @throws HttpRequestException
     * @throws QueryFilterInvalidParamException
     */
    public function getMerchantProducts(string $merchantId): array
    {
        $methods = $this->cachedPaymentMethodsService->getCachedPaymentMethods($merchantId);

        return array_map(function (SeQuraPaymentMethod $method) {
            return $method->getProduct();
        }, $methods);
    }
}
