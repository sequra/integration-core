<?php

namespace SeQura\Core\BusinessLogic\CheckoutAPI\PromotionalWidgets;

use SeQura\Core\BusinessLogic\CheckoutAPI\PromotionalWidgets\Requests\GetWidgetsCheckoutRequest;
use SeQura\Core\BusinessLogic\CheckoutAPI\PromotionalWidgets\Requests\PromotionalWidgetsCheckoutRequest;
use SeQura\Core\BusinessLogic\CheckoutAPI\PromotionalWidgets\Responses\GetWidgetsCheckoutResponse;
use SeQura\Core\BusinessLogic\CheckoutAPI\PromotionalWidgets\Responses\PromotionalWidgetsCheckoutResponse;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Exceptions\PaymentMethodNotFoundException;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Services\WidgetSettingsService;
use SeQura\Core\Infrastructure\Http\Exceptions\HttpRequestException;

/**
 * Class PromotionalWidgetsCheckoutController.
 *
 * @package SeQura\Core\BusinessLogic\CheckoutAPI\PromotionalWidgets
 */
class PromotionalWidgetsCheckoutController
{
    /**
     * @var WidgetSettingsService
     */
    protected $promotionalWidgetsService;


    public function __construct(
        WidgetSettingsService $promotionalWidgetsService
    ) {
        $this->promotionalWidgetsService = $promotionalWidgetsService;
    }

    /**
     * Returns promotional widget initialize data
     *
     * @throws PaymentMethodNotFoundException
     * @throws HttpRequestException
     */
    public function getPromotionalWidgetInitializeData(
        PromotionalWidgetsCheckoutRequest $request
    ): PromotionalWidgetsCheckoutResponse {
        return new PromotionalWidgetsCheckoutResponse($this->promotionalWidgetsService->getWidgetInitializeData(
            $request->getShippingCountry(),
            $request->getCurrentCountry()
        ));
    }

    /**
     * Returns available widget for cart page
     *
     * @throws PaymentMethodNotFoundException
     * @throws HttpRequestException
     */
    public function getAvailableWidgetForCartPage(GetWidgetsCheckoutRequest $request): GetWidgetsCheckoutResponse
    {
        return new GetWidgetsCheckoutResponse($this->promotionalWidgetsService->getAvailableWidgetsForCartPage(
            $request->getMerchantId()
        ));
    }
}
