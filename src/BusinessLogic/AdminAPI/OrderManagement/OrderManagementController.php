<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\OrderManagement;

use Exception;
use SeQura\Core\BusinessLogic\AdminAPI\OrderManagement\Requests\OrderUpdateRequest;
use SeQura\Core\BusinessLogic\AdminAPI\OrderManagement\Responses\OrderUpdateResponse;
use SeQura\Core\BusinessLogic\Domain\Order\Service\OrderService;

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
}
