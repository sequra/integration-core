<?php

namespace SeQura\Core\BusinessLogic\CheckoutAPI\PaymentMethods;

use SeQura\Core\BusinessLogic\CheckoutAPI\PaymentMethods\Requests\PaymentMethodsInCategoriesRequest;
use SeQura\Core\BusinessLogic\CheckoutAPI\PaymentMethods\Responses\PaymentMethodsInCategoriesResponse;
use SeQura\Core\BusinessLogic\Domain\Order\Service\OrderService;
use SeQura\Core\Infrastructure\Http\Exceptions\HttpRequestException;

/**
 * Class PaymentMethodsCheckoutController.
 *
 * Storefront endpoint returning the payment methods of an already solicited order. It depends on OrderService
 * alone, so integrations that solicit orders without configuring the checkout library can use it.
 *
 * @package SeQura\Core\BusinessLogic\CheckoutAPI\PaymentMethods
 */
class PaymentMethodsCheckoutController
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
     * Returns the payment methods available for a solicited order, grouped in the categories SeQura returns them in.
     *
     * @param PaymentMethodsInCategoriesRequest $request
     *
     * @return PaymentMethodsInCategoriesResponse
     *
     * @throws HttpRequestException
     */
    public function getPaymentMethodsInCategories(
        PaymentMethodsInCategoriesRequest $request
    ): PaymentMethodsInCategoriesResponse {
        return new PaymentMethodsInCategoriesResponse(
            $this->orderService->getAvailablePaymentMethodsInCategories(
                $request->getOrderRef(),
                $request->getMerchantId()
            )
        );
    }
}
