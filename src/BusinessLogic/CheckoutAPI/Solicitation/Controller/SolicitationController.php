<?php

namespace SeQura\Core\BusinessLogic\CheckoutAPI\Solicitation\Controller;

use SeQura\Core\BusinessLogic\CheckoutAPI\Solicitation\Response\IdentificationFormResponse;
use SeQura\Core\BusinessLogic\CheckoutAPI\Solicitation\Response\SolicitationResponse;
use SeQura\Core\BusinessLogic\Domain\Order\Builders\CreateOrderRequestBuilder;
use SeQura\Core\BusinessLogic\Domain\Order\Service\OrderService;

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

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function solicitFor(CreateOrderRequestBuilder $builder): SolicitationResponse
    {
        $solicitedOrder = $this->orderService->solicitFor($builder);

        return new SolicitationResponse(
            $solicitedOrder,
            $this->orderService->getAvailablePaymentMethods($solicitedOrder)
        );
    }

    public function getIdentificationForm(
        string $cartId,
        string $product = null,
        string $campaign = null,
        bool $ajax = true
    ) {
        return new IdentificationFormResponse(
            $this->orderService->getIdentificationForm($cartId, $product, $campaign, $ajax)
        );
    }
}
