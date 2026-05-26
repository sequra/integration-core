<?php

namespace SeQura\Core\BusinessLogic\CheckoutAPI\ExpressCheckout\Controller;

use SeQura\Core\BusinessLogic\CheckoutAPI\ExpressCheckout\Requests\ExpressCheckoutAvailabilityRequest;
use SeQura\Core\BusinessLogic\CheckoutAPI\ExpressCheckout\Requests\ExpressCheckoutSolicitRequest;
use SeQura\Core\BusinessLogic\CheckoutAPI\ExpressCheckout\Responses\ExpressCheckoutAvailabilityResponse;
use SeQura\Core\BusinessLogic\CheckoutAPI\Solicitation\Response\IdentificationFormResponse;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\BadMerchantIdException;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionDataNotFoundException;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\CredentialsNotFoundException;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\WrongCredentialsException;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Exceptions\FailedToRetrieveSellingCountriesException;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Services\ExpressCheckoutService;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidUrlException;
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

    /**
     * Solicits the order and returns the identification form for the Express Checkout flow.
     * Called by the platform after the customer clicks the SeQura Express Checkout button.
     *
     * @param ExpressCheckoutSolicitRequest $request
     *
     * @return IdentificationFormResponse
     *
     * @throws HttpRequestException
     * @throws ConnectionDataNotFoundException
     * @throws CredentialsNotFoundException
     * @throws InvalidUrlException
     */
    public function solicit(ExpressCheckoutSolicitRequest $request): IdentificationFormResponse
    {
        return new IdentificationFormResponse(
            $this->expressCheckoutService->solicit($request->getBuilder())
        );
    }
}
