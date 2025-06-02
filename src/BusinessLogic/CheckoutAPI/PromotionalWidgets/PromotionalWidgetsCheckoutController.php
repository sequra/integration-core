<?php

namespace SeQura\Core\BusinessLogic\CheckoutAPI\PromotionalWidgets;

use SeQura\Core\BusinessLogic\CheckoutAPI\PromotionalWidgets\Requests\PromotionalWidgetsCheckoutRequest;
use SeQura\Core\BusinessLogic\CheckoutAPI\PromotionalWidgets\Responses\GetWidgetsCheckoutResponse;
use SeQura\Core\BusinessLogic\CheckoutAPI\PromotionalWidgets\Responses\PromotionalWidgetsCheckoutResponse;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Exceptions\PaymentMethodNotFoundException;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Services\WidgetSettingsService;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Services\WidgetValidationService;
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
    /**
     * @var WidgetValidationService
     */
    protected $widgetValidatorService;

    /**
     * @param WidgetSettingsService $promotionalWidgetsService
     * @param WidgetValidationService $widgetValidatorService
     */
    public function __construct(
        WidgetSettingsService $promotionalWidgetsService,
        WidgetValidationService $widgetValidatorService
    ) {
        $this->promotionalWidgetsService = $promotionalWidgetsService;
        $this->widgetValidatorService = $widgetValidatorService;
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
     * @param PromotionalWidgetsCheckoutRequest $request
     *
     * @return GetWidgetsCheckoutResponse
     * @throws HttpRequestException
     * @throws PaymentMethodNotFoundException
     */
    public function getAvailableWidgetForCartPage(PromotionalWidgetsCheckoutRequest $request): GetWidgetsCheckoutResponse
    {
        if (
            !$this->widgetValidatorService->isCurrencySupported($request->getCurrentCurrency()) ||
            !$this->widgetValidatorService->isIpAddressValid($request->getCurrentIpAddress())
        ) {
            return new GetWidgetsCheckoutResponse([]);
        }

        $availableWidgetForCartPage = $this->promotionalWidgetsService->getAvailableWidgetForCartPage(
            $request->getShippingCountry(),
            $request->getCurrentCountry()
        );

        return new GetWidgetsCheckoutResponse($availableWidgetForCartPage ? [$availableWidgetForCartPage] : []);
    }

    /**
     * Returns available mini-widget for product listing page
     *
     * @param PromotionalWidgetsCheckoutRequest $request
     *
     * @return GetWidgetsCheckoutResponse
     * @throws HttpRequestException
     * @throws PaymentMethodNotFoundException
     */
    public function getAvailableMiniWidgetForProductListingPage(
        PromotionalWidgetsCheckoutRequest $request
    ): GetWidgetsCheckoutResponse {
        if (
            !$this->widgetValidatorService->isCurrencySupported($request->getCurrentCurrency()) ||
            !$this->widgetValidatorService->isIpAddressValid($request->getCurrentIpAddress()) ||
            !$this->widgetValidatorService->isProductSupported($request->getProductId())
        ) {
            return new GetWidgetsCheckoutResponse([]);
        }

        $availableMiniWidgetForProductListingPage = $this->promotionalWidgetsService->getAvailableMiniWidget(
            $request->getShippingCountry(),
            $request->getCurrentCountry()
        );

        return new GetWidgetsCheckoutResponse($availableMiniWidgetForProductListingPage ?
            [$availableMiniWidgetForProductListingPage] : []);
    }

    /**
     * Returns available widgets for product page
     *
     * @param PromotionalWidgetsCheckoutRequest $request
     *
     * @return GetWidgetsCheckoutResponse
     * @throws HttpRequestException
     * @throws PaymentMethodNotFoundException
     */
    public function getAvailableWidgetsForProductPage(
        PromotionalWidgetsCheckoutRequest $request
    ): GetWidgetsCheckoutResponse {
        if (
            !$this->widgetValidatorService->isCurrencySupported($request->getCurrentCurrency()) ||
            !$this->widgetValidatorService->isIpAddressValid($request->getCurrentIpAddress()) ||
            !$this->widgetValidatorService->isProductSupported($request->getProductId())
        ) {
            return new GetWidgetsCheckoutResponse([]);
        }

        return new GetWidgetsCheckoutResponse($this->promotionalWidgetsService->getAvailableWidgetsForProductPage(
            $request->getShippingCountry(),
            $request->getCurrentCountry()
        ));
    }
}
