<?php

namespace SeQura\Core\BusinessLogic\Domain\Order\Service;

use InvalidArgumentException;
use SeQura\Core\BusinessLogic\Domain\Order\Builders\CreateOrderRequestBuilder;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\OrderNotFoundException;
use SeQura\Core\BusinessLogic\Domain\Order\Models\GetAvailablePaymentMethodsRequest;
use SeQura\Core\BusinessLogic\Domain\Order\Models\GetFormRequest;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\CreateOrderRequest;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\UpdateOrderRequest;
use SeQura\Core\BusinessLogic\Domain\Order\Models\SeQuraForm;
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
     *
     * @return SeQuraPaymentMethod[]
     *
     * @throws HttpRequestException
     */
    public function getAvailablePaymentMethods(SeQuraOrder $order): array
    {
        return $this->proxy->getAvailablePaymentMethods(new GetAvailablePaymentMethodsRequest($order->getReference()));
    }

    /**
     * Gets the SeQura form.
     *
     * @param string $cartId
     * @param string|null $product
     * @param string|null $campaign
     * @param bool $ajax
     *
     * @return SeQuraForm
     *
     * @throws HttpRequestException
     */
    public function getIdentificationForm(
        string $cartId,
        string $product = null,
        string $campaign = null,
        bool   $ajax = true
    ): SeQuraForm
    {
        $existingOrder = $this->orderRepository->getByCartId($cartId);
        if (!$existingOrder) {
            throw new InvalidArgumentException(
                "Order form could not be fetched. SeQura order could not be found for cart id ($cartId)."
            );
        }

        return $this->proxy->getForm(new GetFormRequest($existingOrder->getReference(), $product, $campaign, $ajax));
    }

    /**
     * Updates the SeQura order.
     *
     * @param UpdateOrderRequest $request
     *
     * @return SeQuraOrder
     *
     * @throws HttpRequestException
     * @throws OrderNotFoundException
     */
    public function updateOrder(UpdateOrderRequest $request): SeQuraOrder
    {
        $order = $this->getSeQuraOrderFromUpdateRequest($request);
        $this->proxy->updateOrder($request);
        $this->orderRepository->setSeQuraOrder($order);

        return $order;
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

    /**
     * Creates an instance of SeQuraOrder from the UpdateOrderRequest.
     *
     * @param UpdateOrderRequest $request
     *
     * @return SeQuraOrder
     *
     * @throws OrderNotFoundException
     *
     * @noinspection NullPointerExceptionInspection
     */
    private function getSeQuraOrderFromUpdateRequest(UpdateOrderRequest $request): SeQuraOrder
    {
        $shopReference = $request->getMerchantReference()->getOrderRef1();
        $existingOrder = $this->orderRepository->getByShopReference($shopReference);
        if (!$existingOrder) {
            throw new OrderNotFoundException("Order with the shop reference " . $shopReference . " could not be found.");
        }

        $order = (new SeQuraOrder())
            ->setReference($existingOrder->getReference())
            ->setState($existingOrder->getState())
            ->setMerchant($request->getMerchant())
            ->setCart($request->getUnshippedCart())
            ->setDeliveryMethod($request->getDeliveryMethod())
            ->setDeliveryAddress($request->getDeliveryAddress())
            ->setInvoiceAddress($request->getInvoiceAddress())
            ->setCustomer($request->getCustomer())
            ->setPlatform($request->getPlatform())
            ->setGui($existingOrder->getGui());

        if ($request->getUnshippedCart()->getCartRef()) {
            $order->setCartId($request->getUnshippedCart()->getCartRef());
        }

        if ($request->getMerchantReference()) {
            $order->setMerchantReference($request->getMerchantReference());
            $order->setOrderRef1($request->getMerchantReference()->getOrderRef1());
        }

        return $order;
    }
}
