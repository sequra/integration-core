<?php

namespace SeQura\Core\BusinessLogic\CheckoutAPI\Solicitation\Controller;

use SeQura\Core\BusinessLogic\CheckoutAPI\Solicitation\Requests\SolicitationRequest;
use SeQura\Core\BusinessLogic\CheckoutAPI\Solicitation\Response\IdentificationFormResponse;
use SeQura\Core\BusinessLogic\CheckoutAPI\Solicitation\Response\SolicitationResponse;
use SeQura\Core\BusinessLogic\Domain\Checkout\Services\CheckoutService;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\BadMerchantIdException;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionDataNotFoundException;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\CredentialsNotFoundException;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\WrongCredentialsException;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Exceptions\FailedToRetrieveSellingCountriesException;
use SeQura\Core\BusinessLogic\Domain\Order\Builders\PrebuiltCreateOrderRequestBuilder;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidUrlException;
use SeQura\Core\BusinessLogic\Domain\Order\Service\OrderService;
use SeQura\Core\Infrastructure\Http\Exceptions\HttpRequestException;

/**
 * Class SolicitationController
 *
 * @package SeQura\Core\BusinessLogic\CheckoutAPI\Solicitation\Controller
 */
class SolicitationController
{
    /**
     * @var OrderService
     */
    protected $orderService;

    /**
     * @var CheckoutService
     */
    protected $checkoutService;

    /**
     * @param OrderService $orderService
     * @param CheckoutService $checkoutService
     */
    public function __construct(OrderService $orderService, CheckoutService $checkoutService)
    {
        $this->orderService = $orderService;
        $this->checkoutService = $checkoutService;
    }

    /**
     * Solicits the order and returns it together with the payment methods available for it.
     *
     * The eligibility guards run before the solicitation, so an ineligible cart costs no HTTP call.
     *
     * @param SolicitationRequest $request
     *
     * @return SolicitationResponse Successful with a null order and no payment methods when the
     * cart is not eligible — an unsupported shipping country or a GeneralSettings exclusion is an
     * expected shopper state, not an error.
     *
     * @throws BadMerchantIdException
     * @throws ConnectionDataNotFoundException
     * @throws CredentialsNotFoundException
     * @throws FailedToRetrieveSellingCountriesException
     * @throws HttpRequestException
     * @throws InvalidUrlException
     * @throws WrongCredentialsException
     */
    public function solicitFor(SolicitationRequest $request): SolicitationResponse
    {
        $createOrderRequest = $request->getBuilder()->build();

        // The order already carries the shopper's IP, so hosts that pass no explicit one still
        // get the allowlist guard.
        $ipAddress = $request->getIpAddress();
        if ($ipAddress === '') {
            $ipAddress = $createOrderRequest->getCustomer()->getIpNumber();
        }

        $isSupported = $this->checkoutService->isSolicitationSupported(
            $createOrderRequest->getDeliveryAddress()->getCountryCode(),
            $ipAddress,
            $request->getProductIds(),
            $request->getCategoryIds(),
            $request->isCountryCheckEnabled()
        );

        if (!$isSupported) {
            return new SolicitationResponse(null, []);
        }

        // The host builder may not be idempotent, so reuse the request built above.
        $solicitedOrder = $this->orderService->solicitFor(
            new PrebuiltCreateOrderRequestBuilder($createOrderRequest)
        );

        return new SolicitationResponse(
            $solicitedOrder,
            $this->orderService->getAvailablePaymentMethods($solicitedOrder)
        );
    }

    /**
     * @param string $cartId
     * @param string|null $product
     * @param string|null $campaign
     * @param bool $ajax
     *
     * @return IdentificationFormResponse
     *
     * @throws HttpRequestException
     */
    public function getIdentificationForm(
        string $cartId,
        ?string $product = null,
        ?string $campaign = null,
        bool $ajax = true
    ): IdentificationFormResponse {
        return new IdentificationFormResponse(
            $this->orderService->getIdentificationForm($cartId, $product, $campaign, $ajax)
        );
    }
}
