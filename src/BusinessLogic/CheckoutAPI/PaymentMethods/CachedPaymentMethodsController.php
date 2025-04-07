<?php

namespace SeQura\Core\BusinessLogic\CheckoutAPI\PaymentMethods;

use SeQura\Core\BusinessLogic\CheckoutAPI\PaymentMethods\Requests\GetCachedPaymentMethodsRequest;
use SeQura\Core\BusinessLogic\CheckoutAPI\PaymentMethods\Responses\CachedPaymentMethodsResponse;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Exceptions\PaymentMethodNotFoundException;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Services\PaymentMethodsService;
use SeQura\Core\Infrastructure\Http\Exceptions\HttpRequestException;

/**
 * Class CachedPaymentMethodsController.
 *
 * @package SeQura\Core\BusinessLogic\CheckoutAPI\PaymentMethods
 */
class CachedPaymentMethodsController
{
    /**
     * @var PaymentMethodsService $paymentMethodsService
     */
    private $paymentMethodsService;

    /**
     * @param PaymentMethodsService $paymentMethodsService
     */
    public function __construct(PaymentMethodsService $paymentMethodsService)
    {
        $this->paymentMethodsService = $paymentMethodsService;
    }

    /**
     * Returns available payment methods from the database cache.
     *
     * @param GetCachedPaymentMethodsRequest $request
     *
     * @return CachedPaymentMethodsResponse
     *
     * @throws HttpRequestException
     * @throws PaymentMethodNotFoundException
     */
    public function getCachedPaymentMethods(GetCachedPaymentMethodsRequest $request): CachedPaymentMethodsResponse
    {
        return new CachedPaymentMethodsResponse($this->paymentMethodsService->getCachedPaymentMethods($request->getMerchantId()));
    }
}
