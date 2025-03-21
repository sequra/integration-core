<?php

namespace SeQura\Core\BusinessLogic\Domain\PaymentMethod\Services;

use SeQura\Core\BusinessLogic\DataAccess\PaymentMethod\Repositories\PaymentMethodRepository;
use SeQura\Core\BusinessLogic\DataAccess\PaymentMethod\PaymentMethod;
use SeQura\Core\BusinessLogic\Domain\Merchant\Models\GetAvailablePaymentMethodsRequest;
use SeQura\Core\BusinessLogic\Domain\Merchant\ProxyContracts\MerchantProxyInterface;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Exceptions\PaymentMethodNotFoundException;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Models\SeQuraPaymentMethod;
use SeQura\Core\Infrastructure\Http\Exceptions\HttpRequestException;
use SeQura\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;

/**
 * Class CachedPaymentMethodsService
 *
 * @package SeQura\Core\BusinessLogic\Domain\PaymentMethod\Services
 */
class CachedPaymentMethodsService
{
    /**
     * @var PaymentMethodRepository
     */
    protected $paymentMethodsRepository;

    /**
    * @var MerchantProxyInterface
    */
    protected $merchantProxy;


    public function __construct(PaymentMethodRepository $paymentMethodsRepository, MerchantProxyInterface $merchantProxy)
    {
        $this->paymentMethodsRepository = $paymentMethodsRepository;
        $this->merchantProxy = $merchantProxy;
    }

    /**
     * Returns cached SeQura payment methods.
     *
     * @return SeQuraPaymentMethod[]
     *
     * @throws HttpRequestException|QueryFilterInvalidParamException
     */
    public function getCachedPaymentMethods(string $merchantId): array
    {
        $cachedPaymentMethods = $this->paymentMethodsRepository->getPaymentMethods();
        if (!empty($cachedPaymentMethods)) {
            return $this->transformPaymentMethodEntities($cachedPaymentMethods);
        }

        // No cached payment methods found. Fetch them from the API.
        $cachedPaymentMethods = $this->merchantProxy->getAvailablePaymentMethods(new GetAvailablePaymentMethodsRequest($merchantId));

        foreach ($cachedPaymentMethods as $paymentMethod) {
            $cachedPaymentMethod = PaymentMethod::fromArray($paymentMethod->toArray());

            $this->paymentMethodsRepository->setPaymentMethod($cachedPaymentMethod);
        }

        return $cachedPaymentMethods;
    }

    /**
     * Caches payment methods.
     *
     * @param SeQuraPaymentMethod[] $paymentMethods
     *
     * @throws QueryFilterInvalidParamException
     * @throws PaymentMethodNotFoundException
     */
    public function cachePaymentMethods(array $paymentMethods): void
    {
        $cachedPaymentMethods = $this->paymentMethodsRepository->getPaymentMethods();

        $apiProducts = array_map(function (SeQuraPaymentMethod $method) {
                return $method->getProduct();
            }, $paymentMethods);

        $cachedProducts = array_map(function (PaymentMethod $method) {
                return $method->getProduct();
            }, $cachedPaymentMethods);

        $productsToRemove = array_diff($cachedProducts, $apiProducts);
        foreach ($productsToRemove as $product) {
            $this->paymentMethodsRepository->deletePaymentMethodByProductCode($product);
        }

        foreach ($paymentMethods as $paymentMethod) {
            $this->paymentMethodsRepository->setPaymentMethod(PaymentMethod::fromArray($paymentMethod->toArray()));
        }
    }

    /**
     * Transform database representation of payment methods to SeQura payment methods.
     *
     * @param PaymentMethod[] $cachedPaymentMethods
     *
     * @return SeQuraPaymentMethod[]
     *
     * @throws QueryFilterInvalidParamException
     * @throws \Exception
     */
    protected function transformPaymentMethodEntities(array $cachedPaymentMethods): array
    {
        $paymentMethods = [];

        foreach ($cachedPaymentMethods as $cachedPaymentMethod) {
            $paymentMethods[] = SeQuraPaymentMethod::fromArray($cachedPaymentMethod->toArray());
        }

        return $paymentMethods;
    }
}
