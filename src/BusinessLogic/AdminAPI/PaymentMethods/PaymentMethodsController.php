<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\PaymentMethods;

use SeQura\Core\BusinessLogic\AdminAPI\PaymentMethods\Responses\PaymentMethodsResponse;
use SeQura\Core\BusinessLogic\AdminAPI\PaymentMethods\Responses\ProductsResponse;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Services\PaymentMethodsService;
use SeQura\Core\Infrastructure\Http\Exceptions\HttpRequestException;

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
     * @param PaymentMethodsService $paymentMethodsService
     */
    public function __construct(PaymentMethodsService $paymentMethodsService)
    {
        $this->paymentMethodsService = $paymentMethodsService;
    }

    /**
     * Gets all the available payment methods for the merchant.
     *
     * @param string $merchantId
     *
     * @return PaymentMethodsResponse
     *
     * @throws HttpRequestException
     */
    public function getPaymentMethods(string $merchantId): PaymentMethodsResponse
    {
        return new PaymentMethodsResponse($this->paymentMethodsService->getMerchantsPaymentMethods($merchantId));
    }

    /**
     * Gets all products for the merchant.
     *
     * @param string $merchantId
     *
     * @return ProductsResponse
     *
     * @throws HttpRequestException
     */
    public function getProducts(string $merchantId): ProductsResponse
    {
        return new ProductsResponse($this->paymentMethodsService->getMerchantProducts($merchantId));
    }
}
