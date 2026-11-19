<?php

namespace SeQura\Core\BusinessLogic\CheckoutAPI\Checkout\Controller;

use SeQura\Core\BusinessLogic\CheckoutAPI\Checkout\Requests\CheckoutInitializationRequest;
use SeQura\Core\BusinessLogic\CheckoutAPI\Checkout\Responses\CheckoutInitializationResponse;
use SeQura\Core\BusinessLogic\Domain\Checkout\Services\CheckoutInitializationService;
use SeQura\Core\BusinessLogic\Domain\Deployments\Exceptions\DeploymentNotFoundException;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Exceptions\PaymentMethodNotFoundException;
use SeQura\Core\Infrastructure\Http\Exceptions\HttpRequestException;

/**
 * Class CheckoutController.
 *
 * Feature-neutral storefront endpoint that returns the seQura checkout-library
 * bootstrap config (script URL, merchant identity, locale formatting, supported
 * products), so any storefront feature can load the library without depending
 * on the promotional-widgets feature.
 *
 * @package SeQura\Core\BusinessLogic\CheckoutAPI\Checkout\Controller
 */
class CheckoutController
{
    /**
     * @var CheckoutInitializationService
     */
    protected $checkoutInitializationService;

    /**
     * @param CheckoutInitializationService $checkoutInitializationService
     */
    public function __construct(CheckoutInitializationService $checkoutInitializationService)
    {
        $this->checkoutInitializationService = $checkoutInitializationService;
    }

    /**
     * Returns the storefront checkout-library bootstrap config.
     *
     * @param CheckoutInitializationRequest $request
     *
     * @return CheckoutInitializationResponse
     *
     * @throws HttpRequestException
     * @throws PaymentMethodNotFoundException
     * @throws DeploymentNotFoundException
     */
    public function getInitializationData(CheckoutInitializationRequest $request): CheckoutInitializationResponse
    {
        return new CheckoutInitializationResponse($this->checkoutInitializationService->getInitializationData(
            $request->getShippingCountry(),
            $request->getCurrentCountry()
        ));
    }
}
