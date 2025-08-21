<?php

namespace SeQura\Core\BusinessLogic\CheckoutAPI\PaymentMethods;

use SeQura\Core\BusinessLogic\CheckoutAPI\PaymentMethods\Requests\GetCachedPaymentMethodsRequest;
use SeQura\Core\BusinessLogic\CheckoutAPI\PaymentMethods\Responses\CachedPaymentMethodsResponse;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Exceptions\PaymentMethodNotFoundException;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Services\PaymentMethodsService;
use SeQura\Core\Infrastructure\Http\Exceptions\HttpRequestException;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Services\WidgetSettingsService;

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
    * @var WidgetSettingsService $promotionalWidgetsService
    */
    private $promotionalWidgetsService;

    /**
     * @param PaymentMethodsService $paymentMethodsService
     * @param WidgetSettingsService $promotionalWidgetsService
     */
    public function __construct(
        PaymentMethodsService $paymentMethodsService,
        WidgetSettingsService $promotionalWidgetsService
    ) {
        $this->paymentMethodsService = $paymentMethodsService;
        $this->promotionalWidgetsService = $promotionalWidgetsService;
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

    /**
     * Returns payment methods that are supported on product page
     *
     * @param GetCachedPaymentMethodsRequest $request
     *
     * @return CachedPaymentMethodsResponse
     * @throws HttpRequestException
     * @throws PaymentMethodNotFoundException
     */
    public function getCachedPaymentMethodsSupportedOnProductPage(GetCachedPaymentMethodsRequest $request): CachedPaymentMethodsResponse
    {
        $paymentMethods = $this->promotionalWidgetsService->filterPaymentMethodsSupportedOnProductPage(
            $request->getShippingCountry(),
            $request->getCurrentCountry()
        );
        return new CachedPaymentMethodsResponse($paymentMethods);
    }
}
