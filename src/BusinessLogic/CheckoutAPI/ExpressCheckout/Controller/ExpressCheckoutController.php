<?php

namespace SeQura\Core\BusinessLogic\CheckoutAPI\ExpressCheckout\Controller;

use SeQura\Core\BusinessLogic\CheckoutAPI\ExpressCheckout\Requests\ExpressCheckoutAvailabilityRequest;
use SeQura\Core\BusinessLogic\CheckoutAPI\ExpressCheckout\Responses\ExpressCheckoutAvailabilityResponse;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\BadMerchantIdException;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\WrongCredentialsException;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Exceptions\FailedToRetrieveSellingCountriesException;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Services\ExpressCheckoutService;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Exceptions\PaymentMethodNotFoundException;
use SeQura\Core\Infrastructure\Http\Exceptions\HttpRequestException;

/**
 * Class ExpressCheckoutController.
 *
 * @package SeQura\Core\BusinessLogic\CheckoutAPI\ExpressCheckout\Controller
 */
class ExpressCheckoutController
{
    /**
     * @var ExpressCheckoutService
     */
    protected $expressCheckoutService;

    /**
     * @param ExpressCheckoutService $expressCheckoutService
     */
    public function __construct(ExpressCheckoutService $expressCheckoutService)
    {
        $this->expressCheckoutService = $expressCheckoutService;
    }

    /**
     * Returns whether the Express Checkout button should be rendered for the current context.
     *
     * @param ExpressCheckoutAvailabilityRequest $request
     *
     * @return ExpressCheckoutAvailabilityResponse
     *
     * @throws HttpRequestException
     * @throws PaymentMethodNotFoundException
     * @throws BadMerchantIdException
     * @throws WrongCredentialsException
     * @throws FailedToRetrieveSellingCountriesException
     */
    public function isAvailable(ExpressCheckoutAvailabilityRequest $request): ExpressCheckoutAvailabilityResponse
    {
        return new ExpressCheckoutAvailabilityResponse(
            $this->expressCheckoutService->isExpressCheckoutAvailable(
                $request->getPage(),
                $request->getShippingCountry(),
                $request->getCurrency(),
                $request->getIpAddress()
            )
        );
    }
}
