<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\PaymentMethods;

use SeQura\Core\BusinessLogic\AdminAPI\PaymentMethods\Responses\PaymentMethodsResponse;
use SeQura\Core\BusinessLogic\AdminAPI\PaymentMethods\Responses\ProductsResponse;
use SeQura\Core\BusinessLogic\Domain\Merchant\Models\GetAvailablePaymentMethodsRequest;
use SeQura\Core\BusinessLogic\Domain\Merchant\Models\GetPaymentMethodsRequest;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Services\PaymentMethodsService;
use SeQura\Core\Infrastructure\Http\Exceptions\HttpRequestException;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Services\CachedPaymentMethodsService;
use SeQura\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;

/**
 * Class PaymentMethodsController
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\PaymentMethods
 */
class PaymentMethodsController
{
    /**
     * @var PaymentMethodsService
     */
    protected $paymentMethodsService;
    /**
     * @var CachedPaymentMethodsService
     */
    protected $cachedPaymentMethodsService;

    /**
     * @param PaymentMethodsService $paymentMethodsService
     */
    public function __construct(PaymentMethodsService $paymentMethodsService, CachedPaymentMethodsService $cachedPaymentMethodsService)
    {
        $this->paymentMethodsService = $paymentMethodsService;
        $this->cachedPaymentMethodsService = $cachedPaymentMethodsService;
    }

    /**
     * Gets all the available payment methods for the merchant.
     *
     * @param GetPaymentMethodsRequest $request
     * @return PaymentMethodsResponse
     *
     * @throws HttpRequestException
     * @throws QueryFilterInvalidParamException
     */
    public function getPaymentMethods(GetPaymentMethodsRequest $request): PaymentMethodsResponse
    {
        return new PaymentMethodsResponse($this->paymentMethodsService->getMerchantsPaymentMethods($request->getMerchantId(), $request->isCache()));
    }

    /**
     * Returns available payment methods from the database cache.
     *
     * @param GetAvailablePaymentMethodsRequest $request
     *
     * @return PaymentMethodsResponse
     *
     * @throws HttpRequestException
     * @throws QueryFilterInvalidParamException
     */
    public function getCachedPaymentMethods(GetAvailablePaymentMethodsRequest $request): PaymentMethodsResponse
    {
        return new PaymentMethodsResponse($this->cachedPaymentMethodsService->getCachedPaymentMethods($request->getMerchantId()));
    }

    /**
     * Gets all products for the merchant.
     *
     * @param GetAvailablePaymentMethodsRequest $request
     *
     * @return ProductsResponse
     *
     * @throws HttpRequestException
     * @throws QueryFilterInvalidParamException
     */
    public function getProducts(GetAvailablePaymentMethodsRequest $request): ProductsResponse
    {
        return new ProductsResponse($this->paymentMethodsService->getMerchantProducts($request->getMerchantId()));
    }
}
