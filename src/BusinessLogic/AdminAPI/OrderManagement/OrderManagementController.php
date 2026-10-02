<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\OrderManagement;

use Exception;
use SeQura\Core\BusinessLogic\AdminAPI\OrderManagement\Requests\OrderUpdateRequest;
use SeQura\Core\BusinessLogic\AdminAPI\OrderManagement\Requests\UpdateMerchantReferenceRequest;
use SeQura\Core\BusinessLogic\AdminAPI\OrderManagement\Responses\OrderUpdateResponse;
use SeQura\Core\BusinessLogic\AdminAPI\OrderManagement\Responses\UpdateMerchantReferenceResponse;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionDataNotFoundException;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\CredentialsNotFoundException;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\OrderNotFoundException;
use SeQura\Core\BusinessLogic\Domain\Order\Service\OrderService;
use SeQura\Core\BusinessLogic\SeQuraAPI\Exceptions\HttpApiNotFoundException;
use SeQura\Core\Infrastructure\Http\Exceptions\HttpRequestException;

/**
 * Class OrderManagementController
 *
 * What an integration may do to an order seQura already knows.
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\OrderManagement
 */
class OrderManagementController
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
     * Submits the state the order's carts are in now. seQura compares it against the
     * state it was last told and adjusts what it charges the shopper by the difference.
     *
     * @param OrderUpdateRequest $request
     *
     * @return OrderUpdateResponse
     *
     * @throws Exception
     */
    public function updateOrder(OrderUpdateRequest $request): OrderUpdateResponse
    {
        return new OrderUpdateResponse($this->orderService->updateOrder($request->transformToDomainModel()));
    }

    /**
     * Tells seQura the order of the cart is known by a new shop reference from now on, so what the shop reports
     * under that reference still reaches the order.
     *
     * @param UpdateMerchantReferenceRequest $request
     *
     * @return UpdateMerchantReferenceResponse
     *
     * @throws OrderNotFoundException
     * @throws ConnectionDataNotFoundException
     * @throws CredentialsNotFoundException
     * @throws HttpApiNotFoundException
     * @throws HttpRequestException
     */
    public function updateMerchantReference(UpdateMerchantReferenceRequest $request): UpdateMerchantReferenceResponse
    {
        $this->orderService->updateMerchantReference($request->getCartId(), $request->getShopOrderReference());

        return new UpdateMerchantReferenceResponse();
    }
}
