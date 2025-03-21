<?php

namespace SeQura\Core\BusinessLogic\Domain\PaymentMethod\Services;

use SeQura\Core\BusinessLogic\AdminAPI\PaymentMethods\Requests\GetAvailablePaymentMethodsRequest;
use SeQura\Core\BusinessLogic\Domain\Merchant\ProxyContracts\MerchantProxyInterface;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Exceptions\PaymentMethodNotFoundException;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Models\SeQuraPaymentMethod;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\RepositoryContracts\PaymentMethodRepositoryInterface;
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
    protected $merchantProxy;
    /**
     * @var PaymentMethodRepositoryInterface
     */
    protected $paymentMethodsRepository;

    /**
     * @param MerchantProxyInterface $merchantProxy
     * @param PaymentMethodRepositoryInterface $paymentMethodsRepository
     */
    public function __construct(MerchantProxyInterface $merchantProxy, PaymentMethodRepositoryInterface $paymentMethodsRepository)
    {
        $this->merchantProxy = $merchantProxy;
        $this->paymentMethodsRepository = $paymentMethodsRepository;
    }

    /**
     * Gets available payment methods for merchant.
     *
     * @param string $merchantId
     * @param bool $cache
     *
     * @return SeQuraPaymentMethod[]
     *
     * @throws HttpRequestException
     * @throws PaymentMethodNotFoundException
     */
    public function getMerchantsPaymentMethods(string $merchantId, bool $cache = false): array
    {
        $availablePaymentMethods = $this->merchantProxy->getAvailablePaymentMethods(new GetAvailablePaymentMethodsRequest($merchantId));

        if ($cache) {
            $this->cachePaymentMethods($merchantId, $availablePaymentMethods);
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
     */
    public function getMerchantProducts(string $merchantId): array
    {
        $methods = $this->getCachedPaymentMethods($merchantId);

        return array_map(function (SeQuraPaymentMethod $method) {
            return $method->getProduct();
        }, $methods);
    }

    /**
     * Returns cached SeQura payment methods.
     *
     * @return SeQuraPaymentMethod[]
     *
     * @throws HttpRequestException
     */
    public function getCachedPaymentMethods(string $merchantId): array
    {
        $cachedPaymentMethods = $this->paymentMethodsRepository->getPaymentMethods($merchantId);
        if (!empty($cachedPaymentMethods)) {
            return $cachedPaymentMethods;
        }

        // No cached payment methods found. Fetch them from the API.
        $cachedPaymentMethods = $this->merchantProxy->getAvailablePaymentMethods(new GetAvailablePaymentMethodsRequest($merchantId));

        foreach ($cachedPaymentMethods as $paymentMethod) {
            $this->paymentMethodsRepository->setPaymentMethod($merchantId, $paymentMethod);
        }

        return $cachedPaymentMethods;
    }

    /**
     * Caches payment methods.
     *
     * @param string $merchantId
     * @param SeQuraPaymentMethod[] $paymentMethods
     *
     * @throws PaymentMethodNotFoundException
     */
    private function cachePaymentMethods(string $merchantId, array $paymentMethods): void
    {
        $cachedPaymentMethods = $this->paymentMethodsRepository->getPaymentMethods($merchantId);

        $apiProducts = array_map(function (SeQuraPaymentMethod $method) {
            return $method->getProduct();
        }, $paymentMethods);

        $cachedProducts = array_map(function (SeQuraPaymentMethod $method) {
            return $method->getProduct();
        }, $cachedPaymentMethods);

        $productsToRemove = array_diff($cachedProducts, $apiProducts);
        foreach ($productsToRemove as $product) {
            $this->paymentMethodsRepository->deletePaymentMethodByProductCode($product);
        }

        foreach ($paymentMethods as $paymentMethod) {
            $this->paymentMethodsRepository->setPaymentMethod($merchantId, $paymentMethod);
        }
    }
}
