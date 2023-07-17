<?php

namespace SeQura\Core\BusinessLogic\Domain\Order\Service;

use SeQura\Core\BusinessLogic\Domain\Order\Builders\CreateOrderRequestBuilder;
use SeQura\Core\BusinessLogic\Domain\Order\Models\GetAvailablePaymentMethodsRequest;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\CreateOrderRequest;
use SeQura\Core\BusinessLogic\Domain\Order\Models\SeQuraOrder;
use SeQura\Core\BusinessLogic\Domain\Order\ProxyContracts\OrderProxyInterface;
use SeQura\Core\BusinessLogic\Domain\Order\RepositoryContracts\SeQuraOrderRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Models\SeQuraPaymentMethod;
use SeQura\Core\Infrastructure\Http\Exceptions\HttpRequestException;

/**
 * Class OrderService
 *
 * @package SeQura\Core\BusinessLogic\Domain\Order\Service
 */
class OrderService
{
    /**
     * @var OrderProxyInterface
     */
    private $proxy;
    /**
     * @var SeQuraOrderRepositoryInterface
     */
    private $orderRepository;

    public function __construct(OrderProxyInterface $proxy, SeQuraOrderRepositoryInterface $orderRepository)
    {
        $this->proxy = $proxy;
        $this->orderRepository = $orderRepository;
    }

    /**
     * Starts solicitation with a provided builder request data, if order is already solicited it will be updated with
     * provided builder data
     *
     * @return SeQuraOrder Solicited order
     * @throws HttpRequestException
     */
    public function solicitFor(CreateOrderRequestBuilder $builder): SeQuraOrder
    {
        $createOrderRequest = $builder->build();
        $existingOrder = $this->getExistingOrderFor($createOrderRequest);
        if ($existingOrder) {
            $this->orderRepository->deleteOrder($existingOrder);
        }

        $order = $this->proxy->createOrder($createOrderRequest);
        $this->orderRepository->setSeQuraOrder($order);

        return $order;
    }

    /**
     * Gets available payment methods for solicited order
     *
     * @param SeQuraOrder $order
     * @return SeQuraPaymentMethod[]
     * @throws HttpRequestException
     */
    public function getAvailablePaymentMethods(SeQuraOrder $order): array
    {
        return $this->proxy->getAvailablePaymentMethods(new GetAvailablePaymentMethodsRequest($order->getReference()));
    }

    private function getExistingOrderFor(CreateOrderRequest $request): ?SeQuraOrder
    {
        $existingOrder = null;
        if ($request->getCart()->getCartRef()) {
            $existingOrder = $this->orderRepository->getByCartId($request->getCart()->getCartRef());
        }

        if (!$existingOrder && $request->getMerchantReference()) {
            $existingOrder = $this->orderRepository->getByShopReference($request->getMerchantReference()->getOrderRef1());
        }

        return $existingOrder;
    }
}
