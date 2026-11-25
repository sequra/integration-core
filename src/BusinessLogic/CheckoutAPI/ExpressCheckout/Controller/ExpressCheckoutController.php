<?php

namespace SeQura\Core\BusinessLogic\CheckoutAPI\ExpressCheckout\Controller;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\CheckoutAPI\ExpressCheckout\Requests\ExpressCheckoutAvailabilityRequest;
use SeQura\Core\BusinessLogic\CheckoutAPI\ExpressCheckout\Requests\ExpressCheckoutCartUpdateRequest;
use SeQura\Core\BusinessLogic\CheckoutAPI\ExpressCheckout\Requests\ExpressCheckoutSolicitRequest;
use SeQura\Core\BusinessLogic\CheckoutAPI\ExpressCheckout\Requests\GuestExpressCheckoutAvailabilityRequest;
use SeQura\Core\BusinessLogic\CheckoutAPI\ExpressCheckout\Responses\ExpressCheckoutAvailabilityResponse;
use SeQura\Core\BusinessLogic\CheckoutAPI\ExpressCheckout\Responses\ExpressCheckoutCartSummaryResponse;
use SeQura\Core\BusinessLogic\CheckoutAPI\ExpressCheckout\Responses\ExpressCheckoutCartUpdateRejectedResponse;
use SeQura\Core\BusinessLogic\CheckoutAPI\ExpressCheckout\Responses\ExpressCheckoutCartUpdateResponse;
use SeQura\Core\BusinessLogic\CheckoutAPI\ExpressCheckout\Responses\ExpressCheckoutNotThrottledResponse;
use SeQura\Core\BusinessLogic\CheckoutAPI\ExpressCheckout\Responses\ExpressCheckoutSolicitResponse;
use SeQura\Core\BusinessLogic\CheckoutAPI\ExpressCheckout\Responses\ExpressCheckoutThrottledResponse;
use SeQura\Core\BusinessLogic\CheckoutAPI\ExpressCheckout\Responses\ExpressCheckoutUnavailableResponse;
use SeQura\Core\BusinessLogic\CheckoutAPI\ExpressCheckout\Responses\GuestExpressCheckoutAvailabilityResponse;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\BadMerchantIdException;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionDataNotFoundException;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\CredentialsNotFoundException;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\WrongCredentialsException;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Exceptions\FailedToRetrieveSellingCountriesException;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Services\CountryConfigurationService;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Exceptions\ExpressCheckoutCartNotEligibleException;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Exceptions\ExpressCheckoutCartUnavailableException;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Exceptions\InvalidExpressCheckoutCartUpdateException;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Exceptions\InvalidShippingMethodReferenceException;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Services\ExpressCheckoutCartService;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Services\ExpressCheckoutRateLimiter;
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
     * @var ExpressCheckoutCartService
     */
    protected $expressCheckoutCartService;

    /**
     * @var ExpressCheckoutRateLimiter
     */
    protected $rateLimiter;

    /**
     * @param ExpressCheckoutService $expressCheckoutService
     * @param CountryConfigurationService $countryConfigurationService
     * @param ExpressCheckoutCartService $expressCheckoutCartService
     * @param ExpressCheckoutRateLimiter $rateLimiter
     */
    public function __construct(
        ExpressCheckoutService $expressCheckoutService,
        CountryConfigurationService $countryConfigurationService,
        ExpressCheckoutCartService $expressCheckoutCartService,
        ExpressCheckoutRateLimiter $rateLimiter
    ) {
        $this->expressCheckoutService = $expressCheckoutService;
        $this->countryConfigurationService = $countryConfigurationService;
        $this->expressCheckoutCartService = $expressCheckoutCartService;
        $this->rateLimiter = $rateLimiter;
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
        $available = $this->expressCheckoutService->isExpressCheckoutAvailable(
            $request->getPage(),
            $request->getCountry(),
            $request->getCurrency(),
            $request->getIpAddress(),
            $request->getProductIds(),
            $request->getCategoryIds()
        );

        return new ExpressCheckoutAvailabilityResponse(
            $available,
            $available ? $this->resolveButtonStyle() : null
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
        $hasCountries = !empty($countries);

        return new GuestExpressCheckoutAvailabilityResponse(
            $hasCountries,
            $countries,
            $hasCountries ? $this->resolveButtonStyle() : null
        );
    }

    /**
     * Solicits the order and returns the identification form for the Express Checkout flow.
     * Called by the platform after the customer clicks the SeQura Express Checkout button.
     *
     * @param ExpressCheckoutSolicitRequest $request
     *
     * @return ExpressCheckoutSolicitResponse Unsuccessful ({@see ExpressCheckoutUnavailableResponse}) when the request enables the country check and no merchant is configured for the order's delivery country — an expected shopper state, resolved without an exception or a log entry. An ExpressCheckoutThrottledResponse when the call is over its rate limit budget: HTTP 429, no Retry-After, body not part of the contract; plugins call checkRateLimit() before building anything (such as a product-page temporary cart) and test for ExpressCheckoutThrottledResponse before isSuccessful().
     *
     * @throws HttpRequestException
     * @throws ConnectionDataNotFoundException
     * @throws CredentialsNotFoundException
     * @throws InvalidUrlException
     * @throws FailedToRetrieveSellingCountriesException
     */
    public function solicit(ExpressCheckoutSolicitRequest $request): ExpressCheckoutSolicitResponse
    {
        if ($this->rateLimiter->isExceeded()) {
            return new ExpressCheckoutThrottledResponse();
        }

        $form = $this->expressCheckoutService->solicit(
            $request->getBuilder(),
            $request->isCountryCheckEnabled()
        );

        if ($form === null) {
            return new ExpressCheckoutUnavailableResponse();
        }

        return new ExpressCheckoutSolicitResponse(
            $form,
            $form->getOrderUuid(),
            $form->getMetadataEndpoint(),
            $form->getFormUrl()
        );
    }

    /**
     * @param string $cartId
     *
     * @return ExpressCheckoutSolicitResponse An {@see ExpressCheckoutCartSummaryResponse}, or an {@see ExpressCheckoutUnavailableResponse} when the delivery country has no configured merchant. An ExpressCheckoutThrottledResponse when the call is over its rate limit budget: HTTP 429, no Retry-After, body not part of the contract; plugins call checkRateLimit() before building anything (such as a product-page temporary cart) and test for ExpressCheckoutThrottledResponse before isSuccessful().
     *
     * @throws HttpRequestException
     * @throws ConnectionDataNotFoundException
     * @throws CredentialsNotFoundException
     * @throws InvalidUrlException
     * @throws FailedToRetrieveSellingCountriesException
     * @throws \Exception
     */
    public function solicitCartSummary(string $cartId): ExpressCheckoutSolicitResponse
    {
        if ($this->rateLimiter->isExceeded()) {
            return new ExpressCheckoutThrottledResponse();
        }

        $summary = $this->expressCheckoutCartService->solicitCartSummary($cartId);
        if ($summary === null) {
            return new ExpressCheckoutUnavailableResponse();
        }

        $form = $summary->getForm();

        return new ExpressCheckoutCartSummaryResponse(
            $form,
            $form->getOrderUuid(),
            $form->getMetadataEndpoint(),
            $form->getFormUrl(),
            $summary->getFlowToken(),
            $summary->getCartData()
        );
    }

    /**
     * @param ExpressCheckoutCartUpdateRequest $request
     *
     * @return Response HTTP status contract: ExpressCheckoutCartUpdateResponse is 200 with toArray() as the JSON body; ExpressCheckoutCartUpdateRejectedResponse is 400 for invalid_request and cart_unavailable and 422 for not_eligible; any other unsuccessful response is 500 (core has already logged it); the plugin's failed CSRF check is 403; ExpressCheckoutThrottledResponse is HTTP 429, no Retry-After, body not part of the contract; plugins call checkRateLimit() before building anything (such as a product-page temporary cart) and test for ExpressCheckoutThrottledResponse before isSuccessful().
     *
     * @throws HttpRequestException
     * @throws ConnectionDataNotFoundException
     * @throws CredentialsNotFoundException
     * @throws InvalidUrlException
     * @throws FailedToRetrieveSellingCountriesException
     */
    public function updateCart(ExpressCheckoutCartUpdateRequest $request): Response
    {
        if ($this->rateLimiter->isExceeded()) {
            return new ExpressCheckoutThrottledResponse();
        }

        try {
            return new ExpressCheckoutCartUpdateResponse(
                $this->expressCheckoutCartService->updateCart($request->getFlowToken(), $request->getPayload())
            );
        } catch (InvalidExpressCheckoutCartUpdateException $exception) {
            return new ExpressCheckoutCartUpdateRejectedResponse(
                ExpressCheckoutCartUpdateRejectedResponse::REASON_INVALID_REQUEST
            );
        } catch (InvalidShippingMethodReferenceException $exception) {
            return new ExpressCheckoutCartUpdateRejectedResponse(
                ExpressCheckoutCartUpdateRejectedResponse::REASON_INVALID_REQUEST
            );
        } catch (ExpressCheckoutCartUnavailableException $exception) {
            return new ExpressCheckoutCartUpdateRejectedResponse(
                ExpressCheckoutCartUpdateRejectedResponse::REASON_CART_UNAVAILABLE
            );
        } catch (ExpressCheckoutCartNotEligibleException $exception) {
            return new ExpressCheckoutCartUpdateRejectedResponse(
                ExpressCheckoutCartUpdateRejectedResponse::REASON_NOT_ELIGIBLE
            );
        }
    }

    /**
     * @return Response An ExpressCheckoutNotThrottledResponse, or an ExpressCheckoutThrottledResponse when the next solicit, solicitCartSummary or updateCart call would be refused; counts nothing. ExpressCheckoutThrottledResponse is HTTP 429, no Retry-After, body not part of the contract; plugins call checkRateLimit() before building anything (such as a product-page temporary cart) and test for ExpressCheckoutThrottledResponse before isSuccessful().
     */
    public function checkRateLimit(): Response
    {
        return $this->rateLimiter->wouldBeExceeded()
            ? new ExpressCheckoutThrottledResponse()
            : new ExpressCheckoutNotThrottledResponse();
    }

    /**
     * @return string|null
     */
    private function resolveButtonStyle(): ?string
    {
        $settings = $this->expressCheckoutService->getExpressCheckoutSettings();

        return $settings ? $settings->getButtonStyle() : null;
    }
}
