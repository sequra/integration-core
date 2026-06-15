<?php

namespace SeQura\Core\BusinessLogic\CheckoutAPI\ExpressCheckout\Controller;

use SeQura\Core\BusinessLogic\CheckoutAPI\ExpressCheckout\Requests\ExpressCheckoutAvailabilityRequest;
use SeQura\Core\BusinessLogic\CheckoutAPI\ExpressCheckout\Requests\ExpressCheckoutSolicitRequest;
use SeQura\Core\BusinessLogic\CheckoutAPI\ExpressCheckout\Requests\GuestExpressCheckoutAvailabilityRequest;
use SeQura\Core\BusinessLogic\CheckoutAPI\ExpressCheckout\Responses\ExpressCheckoutAvailabilityResponse;
use SeQura\Core\BusinessLogic\CheckoutAPI\ExpressCheckout\Responses\ExpressCheckoutUnavailableResponse;
use SeQura\Core\BusinessLogic\CheckoutAPI\ExpressCheckout\Responses\GuestExpressCheckoutAvailabilityResponse;
use SeQura\Core\BusinessLogic\CheckoutAPI\Solicitation\Response\IdentificationFormResponse;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\BadMerchantIdException;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionDataNotFoundException;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\CredentialsNotFoundException;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\WrongCredentialsException;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Exceptions\FailedToRetrieveSellingCountriesException;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Services\CountryConfigurationService;
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
     * @var CountryConfigurationService
     */
    protected $countryConfigurationService;

    /**
     * @param ExpressCheckoutService $expressCheckoutService
     * @param CountryConfigurationService $countryConfigurationService
     */
    public function __construct(
        ExpressCheckoutService $expressCheckoutService,
        CountryConfigurationService $countryConfigurationService
    ) {
        $this->expressCheckoutService = $expressCheckoutService;
        $this->countryConfigurationService = $countryConfigurationService;
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
                $request->getCountry(),
                $request->getCurrency(),
                $request->getIpAddress(),
                $request->getProductIds(),
                $request->getCategoryIds()
            )
        );
    }

    /**
     * Returns guest Express Checkout availability for the current context. The shipping country is
     * unknown for guests, so the country guard is skipped; the response carries the availability
     * flag plus the configured selling countries the storefront can offer once an address is known.
     *
     * @param GuestExpressCheckoutAvailabilityRequest $request
     *
     * @return GuestExpressCheckoutAvailabilityResponse
     *
     * @throws HttpRequestException
     * @throws BadMerchantIdException
     * @throws WrongCredentialsException
     * @throws FailedToRetrieveSellingCountriesException
     */
    public function isAvailableForGuest(
        GuestExpressCheckoutAvailabilityRequest $request
    ): GuestExpressCheckoutAvailabilityResponse {
        $available = $this->expressCheckoutService->isAvailableForGuest(
            $request->getPage(),
            $request->getCurrency(),
            $request->getIpAddress(),
            $request->getProductIds(),
            $request->getCategoryIds()
        );

        $countries = $available ? $this->countryConfigurationService->getCountryCodes() : [];

        return new GuestExpressCheckoutAvailabilityResponse(!empty($countries), $countries);
    }

    /**
     * Solicits the order and returns the identification form for the Express Checkout flow.
     * Called by the platform after the customer clicks the SeQura Express Checkout button.
     *
     * @param ExpressCheckoutSolicitRequest $request
     *
     * @return IdentificationFormResponse Unsuccessful ({@see ExpressCheckoutUnavailableResponse})
     * when the request enables the country check and no merchant is configured for the order's
     * delivery country — an expected shopper state, resolved without an exception or a log entry.
     *
     * @throws HttpRequestException
     * @throws ConnectionDataNotFoundException
     * @throws CredentialsNotFoundException
     * @throws InvalidUrlException
     * @throws FailedToRetrieveSellingCountriesException
     */
    public function solicit(ExpressCheckoutSolicitRequest $request): IdentificationFormResponse
    {
        $form = $this->expressCheckoutService->solicit(
            $request->getBuilder(),
            $request->isCountryCheckEnabled()
        );

        if ($form === null) {
            return new ExpressCheckoutUnavailableResponse();
        }

        return new IdentificationFormResponse($form);
    }
}
