<?php

namespace SeQura\Core\BusinessLogic\Domain\PaymentMethod\Services;

use SeQura\Core\BusinessLogic\AdminAPI\PaymentMethods\Requests\GetAvailablePaymentMethodsRequest;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Services\CountryConfigurationService;
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
     * @var CountryConfigurationService
     */
    protected $countryConfigurationService;

    /**
     * @param MerchantProxyInterface $merchantProxy
     * @param PaymentMethodRepositoryInterface $paymentMethodsRepository
     * @param CountryConfigurationService $countryConfigurationService
     */
    public function __construct(
        MerchantProxyInterface           $merchantProxy,
        PaymentMethodRepositoryInterface $paymentMethodsRepository,
        CountryConfigurationService      $countryConfigurationService
    )
    {
        $this->merchantProxy = $merchantProxy;
        $this->paymentMethodsRepository = $paymentMethodsRepository;
        $this->countryConfigurationService = $countryConfigurationService;
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
     *  Returns available payment methods for all enabled merchants.
     *
     * @param bool $cache
     *
     * @return SeQuraPaymentMethod[]
     * @throws HttpRequestException
     * @throws PaymentMethodNotFoundException
     */
    public function getAvailablePaymentMethodsForAllMerchants(bool $cache = false): array
    {
        $availablePaymentMethods = [];
        $merchantIds = $this->getMerchantIds();

        foreach ($merchantIds as $merchantId) {
            $methods = $this->getMerchantsPaymentMethods($merchantId, $cache);

            if (!empty($methods)) {
                $availablePaymentMethods[] = $methods;
            }
        }

        return !empty($availablePaymentMethods) ? array_merge(...$availablePaymentMethods) : [];
    }

    /**
     * Gets available products for the merchant.
     *
     * @param string $merchantId
     *
     * @return string[]
     *
     * @throws HttpRequestException
     * @throws PaymentMethodNotFoundException
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
     * @throws PaymentMethodNotFoundException
     */
    public function getCachedPaymentMethods(string $merchantId): array
    {
        $cachedPaymentMethods = $this->paymentMethodsRepository->getPaymentMethods($merchantId);
        if (!empty($cachedPaymentMethods)) {
            return $cachedPaymentMethods;
        }

        // No cached payment methods found. Fetch them from the API as fallback.
        return $this->getMerchantsPaymentMethods($merchantId, true);
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
        $this->paymentMethodsRepository->deletePaymentMethods($merchantId);

        foreach ($paymentMethods as $paymentMethod) {
            $this->paymentMethodsRepository->setPaymentMethod($merchantId, $paymentMethod);
        }
    }

    /**
     * Retrieves available merchant ids from database.
     *
     * @return string[]
     */
    private function getMerchantIds(): array
    {
        $countryConfigurations = $this->countryConfigurationService->getCountryConfiguration();
        if (empty($countryConfigurations)) {
            return [];
        }

        return array_map(function ($config) {
            return $config->getMerchantId();
        }, $countryConfigurations);
    }
}
