<?php

namespace SeQura\Core\BusinessLogic\CheckoutAPI\Solicitation\Controller;

use SeQura\Core\BusinessLogic\CheckoutAPI\Solicitation\Requests\SolicitationRequest;
use SeQura\Core\BusinessLogic\CheckoutAPI\Solicitation\Response\IdentificationFormResponse;
use SeQura\Core\BusinessLogic\CheckoutAPI\Solicitation\Response\SolicitationResponse;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\BadMerchantIdException;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\WrongCredentialsException;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Exceptions\FailedToRetrieveSellingCountriesException;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidUrlException;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\OrderMerchantNotFoundException;
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
     * @param OrderService $orderService
     */
    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
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
     * @throws FailedToRetrieveSellingCountriesException
     * @throws HttpRequestException
     * @throws InvalidUrlException
     * @throws WrongCredentialsException|OrderMerchantNotFoundException
     */
    public function solicitFor(SolicitationRequest $request): SolicitationResponse
    {
        $solicitedOrder = $this->orderService->solicitIfSupported(
            $request->getBuilder(),
            $request->getProductIds(),
            $request->getCategoryIds(),
            $request->isCountryCheckEnabled()
        );

        if (!$solicitedOrder) {
            return new SolicitationResponse(null, []);
        }

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
