<?php

namespace SeQura\Core\BusinessLogic\CheckoutAPI\PromotionalWidgets;

use SeQura\Core\BusinessLogic\CheckoutAPI\PromotionalWidgets\Requests\PromotionalWidgetsCheckoutRequest;
use SeQura\Core\BusinessLogic\CheckoutAPI\PromotionalWidgets\Responses\GetWidgetsCheckoutResponse;
use SeQura\Core\BusinessLogic\CheckoutAPI\PromotionalWidgets\Responses\PromotionalWidgetsCheckoutResponse;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\BadMerchantIdException;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\WrongCredentialsException;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Exceptions\FailedToRetrieveSellingCountriesException;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Exceptions\PaymentMethodNotFoundException;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Services\WidgetSettingsService;
use SeQura\Core\BusinessLogic\Domain\Checkout\Services\CheckoutService;
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
     * @var CheckoutService
     */
    protected $checkoutValidationService;

    /**
     * @param WidgetSettingsService $promotionalWidgetsService
     * @param CheckoutService $checkoutValidationService
     */
    public function __construct(
        WidgetSettingsService $promotionalWidgetsService,
        CheckoutService $checkoutValidationService
    ) {
        $this->promotionalWidgetsService = $promotionalWidgetsService;
        $this->checkoutValidationService = $checkoutValidationService;
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
     * @throws BadMerchantIdException
     * @throws WrongCredentialsException
     * @throws FailedToRetrieveSellingCountriesException
     */
    public function getAvailableWidgetForCartPage(PromotionalWidgetsCheckoutRequest $request): GetWidgetsCheckoutResponse
    {
        $isSupported = $this->checkoutValidationService->isWidgetSupported(
            $request->getCurrentCurrency(),
            $request->getCurrentIpAddress()
        );
        if (!$isSupported) {
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
     *
     * @throws BadMerchantIdException
     * @throws FailedToRetrieveSellingCountriesException
     * @throws HttpRequestException
     * @throws PaymentMethodNotFoundException
     * @throws WrongCredentialsException
     */
    public function getAvailableMiniWidgetForProductListingPage(
        PromotionalWidgetsCheckoutRequest $request
    ): GetWidgetsCheckoutResponse {
        $isSupported = $this->checkoutValidationService->isWidgetSupported(
            $request->getCurrentCurrency(),
            $request->getCurrentIpAddress(),
            $request->getProductId()
        );
        if (!$isSupported) {
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
     *
     * @throws BadMerchantIdException
     * @throws FailedToRetrieveSellingCountriesException
     * @throws HttpRequestException
     * @throws PaymentMethodNotFoundException
     * @throws WrongCredentialsException
     */
    public function getAvailableWidgetsForProductPage(
        PromotionalWidgetsCheckoutRequest $request
    ): GetWidgetsCheckoutResponse {
        $isSupported = $this->checkoutValidationService->isWidgetSupported(
            $request->getCurrentCurrency(),
            $request->getCurrentIpAddress(),
            $request->getProductId()
        );
        if (!$isSupported) {
            return new GetWidgetsCheckoutResponse([]);
        }

        return new GetWidgetsCheckoutResponse($this->promotionalWidgetsService->getAvailableWidgetsForProductPage(
            $request->getShippingCountry(),
            $request->getCurrentCountry()
        ));
    }
}
