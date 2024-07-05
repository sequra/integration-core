<?php

namespace SeQura\Core\BusinessLogic\Webhook\Handler;

use Exception;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidOrderStateException;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\CreateOrderRequest;
use SeQura\Core\BusinessLogic\Domain\Order\Models\SeQuraOrder;
use SeQura\Core\BusinessLogic\Domain\Order\OrderRequestStatusMapping;
use SeQura\Core\BusinessLogic\Domain\Order\OrderStates;
use SeQura\Core\BusinessLogic\Domain\Webhook\Models\Webhook;
use SeQura\Core\BusinessLogic\SeQuraAPI\Order\OrderProxy;
use SeQura\Core\BusinessLogic\Domain\Order\ProxyContracts\OrderProxyInterface;
use SeQura\Core\BusinessLogic\Webhook\Services\ShopOrderService;
use SeQura\Core\BusinessLogic\Webhook\Tasks\OrderUpdateTask;
use SeQura\Core\Infrastructure\Http\Exceptions\HttpRequestException;
use SeQura\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use SeQura\Core\Infrastructure\ORM\QueryFilter\Operators;
use SeQura\Core\Infrastructure\ORM\QueryFilter\QueryFilter;
use SeQura\Core\Infrastructure\ORM\RepositoryRegistry;
use SeQura\Core\Infrastructure\ServiceRegister;

/**
 * Class WebhookHandler
 *
 * @package SeQura\Core\BusinessLogic\Webhook\Handler
 */
class WebhookHandler
{
    /**
     * Handles an incoming webhook request.
     *
     * @param Webhook $webhook
     *
     * @return void
     *
     * @throws HttpRequestException
     * @throws InvalidOrderStateException
     * @throws QueryFilterInvalidParamException
     * @throws RepositoryNotRegisteredException
     * @throws Exception
     */
    public function handle(Webhook $webhook): void
    {
        $task = new OrderUpdateTask($webhook);
        $task->execute();

        if (in_array($webhook->getSqState(), [OrderStates::STATE_APPROVED, OrderStates::STATE_NEEDS_REVIEW], true)) {
            $this->acknowledgeOrder($webhook->getOrderRef(), $webhook->getSqState());
        }
    }


    /**
     * Acknowledges the order and its state to SeQura API.
     *
     * @param string $orderReference
     * @param string $state
     *
     * @return void
     *
     * @throws HttpRequestException
     * @throws QueryFilterInvalidParamException
     * @throws RepositoryNotRegisteredException
     * @throws InvalidOrderStateException
     */
    protected function acknowledgeOrder(string $orderReference, string $state): void
    {
        /**
        * @var OrderProxy $orderProxy
        */
        $orderProxy = ServiceRegister::getService(OrderProxyInterface::class);
        $shopOrder = $this->getCreateOrderRequest($orderReference);

        $request = new CreateOrderRequest(
            OrderRequestStatusMapping::mapOrderRequestStatus($state),
            $shopOrder->getMerchant(),
            $shopOrder->getCart(),
            $shopOrder->getDeliveryMethod(),
            $shopOrder->getCustomer(),
            $shopOrder->getPlatform(),
            $shopOrder->getDeliveryAddress(),
            $shopOrder->getInvoiceAddress(),
            $shopOrder->getGui(),
            $shopOrder->getMerchantReference()
        );

        $orderProxy->acknowledgeOrder($orderReference, $request);
    }

    /**
     * Retrieves the SeQuraOrder by orderRef1
     *
     * @param string $orderRef
     *
     * @return SeQuraOrder|null
     *
     * @throws QueryFilterInvalidParamException
     * @throws RepositoryNotRegisteredException
     */
    protected function getSeQuraOrderByOrderReference(string $orderRef): ?SeQuraOrder
    {
        $repository = RepositoryRegistry::getRepository(SeQuraOrder::getClassName());

        $filter = new QueryFilter();
        $filter->where('reference', Operators::EQUALS, $orderRef);

        /**
        * @var SeQuraOrder $order
        */
        $order = $repository->selectOne($filter);

        return $order;
    }

    /**
     * Retrieves create order request.
     *
     * @param string $orderReference
     *
     * @return CreateOrderRequest
     */
    protected function getCreateOrderRequest(string $orderReference): CreateOrderRequest
    {
        /** @var ShopOrderService $service */
        $service = ServiceRegister::getService(ShopOrderService::class);

        return $service->getCreateOrderRequest($orderReference);
    }
}
