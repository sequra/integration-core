<?php

namespace SeQura\Core\BusinessLogic\Domain\Order\Service;

use Exception;
use InvalidArgumentException;
use SeQura\Core\BusinessLogic\Domain\Order\Builders\CreateOrderRequestBuilder;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\OrderNotFoundException;
use SeQura\Core\BusinessLogic\Domain\Order\Models\GetAvailablePaymentMethodsRequest;
use SeQura\Core\BusinessLogic\Domain\Order\Models\GetFormRequest;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\CreateOrderRequest;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\UpdateOrderRequest;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderUpdateData;
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
     * Gets the SeQuraOrder for a given shop order reference.
     *
     * @param string $shopReference
     *
     * @return SeQuraOrder
     *
     * @throws OrderNotFoundException
     */
    public function getOrderByShopReference(string $shopReference): SeQuraOrder
    {
        $order = $this->orderRepository->getByShopReference($shopReference);
        if(!$order){
            throw new OrderNotFoundException('Order for shop reference ' . $shopReference . ' not found.');
        }

        return $order;
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
     * @param OrderUpdateData $orderUpdateData
     *
     * @return SeQuraOrder
     *
     * @throws Exception
     */
    public function updateOrder(OrderUpdateData $orderUpdateData): SeQuraOrder
    {
        $order = $this->getOrderByShopReference($orderUpdateData->getOrderShopReference());
        $hasChanges = false;

        $newShippedCart = $orderUpdateData->getShippedCart();
        if($newShippedCart && !$this->areObjectsEqual($order->getShippedCart(), $newShippedCart)) {
            $order->setShippedCart($newShippedCart);
            $hasChanges = true;
        }

        $newUnshippedCart = $orderUpdateData->getUnshippedCart();
        if($newUnshippedCart && !$this->areObjectsEqual($order->getUnshippedCart(), $newUnshippedCart)) {
            $order->setUnshippedCart($newUnshippedCart);
            $hasChanges = true;
        }

        $newInvoiceAddress = $orderUpdateData->getInvoiceAddress();
        if($newInvoiceAddress && !$this->areObjectsEqual($order->getInvoiceAddress(), $newInvoiceAddress)) {
            $order->setInvoiceAddress($newInvoiceAddress);
            $hasChanges = true;
        }

        $newDeliveryAddress = $orderUpdateData->getDeliveryAddress();
        if($newDeliveryAddress && !$this->areObjectsEqual($order->getDeliveryAddress(), $newDeliveryAddress)) {
            $order->setDeliveryAddress($newDeliveryAddress);
            $hasChanges = true;
        }

        if($hasChanges) {
            $this->proxy->updateOrder($this->getUpdateOrderRequest($order));
            $this->orderRepository->setSeQuraOrder($order);
        }

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
     * Creates an instance of UpdateOrderRequest.
     *
     * @param SeQuraOrder $order
     *
     * @return UpdateOrderRequest
     *
     * @throws Exception
     */
    private function getUpdateOrderRequest(SeQuraOrder $order): UpdateOrderRequest
    {
        return UpdateOrderRequest::fromArray([
            'merchant' => $order->getMerchant()->toArray(),
            'merchant_reference' => $order->getMerchantReference()->toArray(),
            'unshipped_cart' => $order->getUnshippedCart()->toArray(),
            'shipped_cart' =>  $order->getShippedCart()->toArray(),
            'trackings' => $order->getTrackings(),
            'delivery_method' => $order->getDeliveryMethod()->toArray(),
            'delivery_address' => $order->getDeliveryAddress()->toArray(),
            'invoice_address' => $order->getInvoiceAddress()->toArray(),
            'customer' => $order->getCustomer()->toArray(),
            'platform' => $order->getPlatform()->toArray(),
        ]);
    }

    /**
     * Checks if the objects are equal.
     *
     * @param $object1
     * @param $object2
     *
     * @return bool
     */
    private function areObjectsEqual($object1, $object2):bool
    {
        if (method_exists($object1, 'toArray') && method_exists($object2, 'toArray')) {
            return json_encode($object1->toArray()) === json_encode($object2->toArray());
        }

        return json_encode($object1) === json_encode($object2);
    }
}