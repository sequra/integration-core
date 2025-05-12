<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\PaymentMethods;

use SeQura\Core\BusinessLogic\AdminAPI\PaymentMethods\Requests\GetFormattedPaymentMethodsRequest;
use SeQura\Core\BusinessLogic\AdminAPI\PaymentMethods\Requests\GetAvailablePaymentMethodsRequest;
use SeQura\Core\BusinessLogic\AdminAPI\PaymentMethods\Requests\GetPaymentMethodsRequest;
use SeQura\Core\BusinessLogic\AdminAPI\PaymentMethods\Responses\FormattedPaymentMethodsResponse;
use SeQura\Core\BusinessLogic\AdminAPI\PaymentMethods\Responses\PaymentMethodsResponse;
use SeQura\Core\BusinessLogic\AdminAPI\PaymentMethods\Responses\ProductsResponse;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Exceptions\PaymentMethodNotFoundException;
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
     * @param GetPaymentMethodsRequest $request
     *
     * @return PaymentMethodsResponse
     *
     * @throws HttpRequestException
     * @throws PaymentMethodNotFoundException
     */
    public function getPaymentMethods(GetPaymentMethodsRequest $request): PaymentMethodsResponse
    {
        return new PaymentMethodsResponse($this->paymentMethodsService->getMerchantsPaymentMethods($request->getMerchantId(), $request->isCache()));
    }

    /**
     * Returns available payment method titles for all merchants, grouped by category and product type.
     *
     * @param GetFormattedPaymentMethodsRequest $request
     * @return FormattedPaymentMethodsResponse
     * @throws HttpRequestException
     * @throws PaymentMethodNotFoundException
     */
    public function getAllAvailablePaymentMethods(GetFormattedPaymentMethodsRequest $request): FormattedPaymentMethodsResponse
    {
        return new FormattedPaymentMethodsResponse(
            $this->paymentMethodsService->getAvailablePaymentMethodsForAllMerchants($request->isCache())
        );
    }

    /**
     * Gets all products for the merchant.
     *
     * @param GetAvailablePaymentMethodsRequest $request
     *
     * @return ProductsResponse
     *
     * @throws HttpRequestException
     * @throws PaymentMethodNotFoundException
     */
    public function getProducts(GetAvailablePaymentMethodsRequest $request): ProductsResponse
    {
        return new ProductsResponse($this->paymentMethodsService->getMerchantProducts($request->getMerchantId()));
    }
}
