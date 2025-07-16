<?php

namespace SeQura\Core\BusinessLogic\Domain\Order\Service;

use Exception;
use InvalidArgumentException;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionDataNotFoundException;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\CredentialsNotFoundException;
use SeQura\Core\BusinessLogic\Domain\Integration\Order\OrderCreationInterface;
use SeQura\Core\BusinessLogic\Domain\Order\Builders\CreateOrderRequestBuilder;
use SeQura\Core\BusinessLogic\Domain\Order\Builders\MerchantOrderRequestBuilder;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidOrderStateException;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidUrlException;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\OrderNotFoundException;
use SeQura\Core\BusinessLogic\Domain\Order\Models\GetAvailablePaymentMethodsRequest;
use SeQura\Core\BusinessLogic\Domain\Order\Models\GetFormRequest;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\CreateOrderRequest;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\MerchantReference;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\OrderRequestStates;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\UpdateOrderRequest;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderUpdateData;
use SeQura\Core\BusinessLogic\Domain\Order\Models\PaymentMethod;
use SeQura\Core\BusinessLogic\Domain\Order\Models\SeQuraForm;
use SeQura\Core\BusinessLogic\Domain\Order\Models\SeQuraOrder;
use SeQura\Core\BusinessLogic\Domain\Order\OrderRequestStatusMapping;
use SeQura\Core\BusinessLogic\Domain\Order\ProxyContracts\OrderProxyInterface;
use SeQura\Core\BusinessLogic\Domain\Order\RepositoryContracts\SeQuraOrderRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Models\SeQuraPaymentMethod;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Models\SeQuraPaymentMethodCategory;
use SeQura\Core\BusinessLogic\Domain\Webhook\Models\Webhook;
use SeQura\Core\BusinessLogic\SeQuraAPI\Exceptions\HttpApiNotFoundException;
use SeQura\Core\Infrastructure\Http\Exceptions\HttpRequestException;

/**
 * Class OrderService
 *
 * @package SeQura\Core\BusinessLogic\Domain\Order\Service
 */
class OrderService
{
    /**
     * Product codes for installment payments category.
     */
    private const INSTALLMENT_METHOD_CODES = ['pp3', 'pp6', 'pp9'];
    /**
     * @var OrderProxyInterface
     */
    protected $proxy;
    /**
     * @var OrderCreationInterface
     */
    protected $shopOrderCreator;
    /**
     * @var SeQuraOrderRepositoryInterface
     */
    protected $orderRepository;
    /**
     * @var MerchantOrderRequestBuilder
     */
    protected $merchantOrderRequestBuilder;

    /**
     * @param OrderProxyInterface $proxy
     * @param SeQuraOrderRepositoryInterface $orderRepository
     * @param MerchantOrderRequestBuilder $merchantOrderRequestBuilder
     * @param OrderCreationInterface $shopOrderCreator
     */
    public function __construct(
        OrderProxyInterface $proxy,
        SeQuraOrderRepositoryInterface $orderRepository,
        MerchantOrderRequestBuilder $merchantOrderRequestBuilder,
        OrderCreationInterface $shopOrderCreator
    ) {
        $this->proxy = $proxy;
        $this->orderRepository = $orderRepository;
        $this->merchantOrderRequestBuilder = $merchantOrderRequestBuilder;
        $this->shopOrderCreator = $shopOrderCreator;
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
        if (!$order) {
            throw new OrderNotFoundException('Order for shop reference ' . $shopReference . ' not found.');
        }

        return $order;
    }

    /**
     * Gets a batch of orders for a given array of shop references, sorted according to the given shop reference array.
     *
     * @param string[] $shopReferences
     *
     * @return SeQuraOrder[]
     */
    public function getOrderBatchForShopReferences(array $shopReferences): array
    {
        return $this->orderRepository->getOrderBatchByShopReferences($shopReferences);
    }

    /**
     * Starts solicitation with a provided builder request data, if order is already solicited it will be updated with
     * provided builder data
     *
     * @param CreateOrderRequestBuilder $builder
     *
     * @return SeQuraOrder Solicited order
     *
     * @throws HttpRequestException
     * @throws ConnectionDataNotFoundException
     * @throws CredentialsNotFoundException
     * @throws InvalidUrlException
     */
    public function solicitFor(CreateOrderRequestBuilder $builder): SeQuraOrder
    {
        $createOrderRequest = $builder->build();

        if (!$createOrderRequest->getMerchant()) {
            $createOrderRequest->setMerchant($this->merchantOrderRequestBuilder->build(
                $createOrderRequest->getDeliveryAddress()->getCountryCode(),
                $createOrderRequest->getCart()->getCartRef()
            ));
        }

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
        return $this->proxy->getAvailablePaymentMethods(
            new GetAvailablePaymentMethodsRequest(
                $order->getReference(),
                $order->getMerchant()->getId()
            )
        );
    }

    /**
     * Gets available payment methods for solicited order in categories.
     *
     * @param string $orderRef
     * @param string $merchantId
     *
     * @return SeQuraPaymentMethodCategory[]
     *
     * @throws HttpRequestException
     */
    public function getAvailablePaymentMethodsInCategories(string $orderRef, string $merchantId): array
    {
        return $this->proxy->getAvailablePaymentMethodsInCategories(
            new GetAvailablePaymentMethodsRequest($orderRef, $merchantId)
        );
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
        bool $ajax = true
    ): SeQuraForm {
        $existingOrder = $this->orderRepository->getByCartId($cartId);
        if (!$existingOrder) {
            throw new InvalidArgumentException(
                "Order form could not be fetched. SeQura order could not be found for cart id ($cartId)."
            );
        }

        return $this->proxy->getForm(
            new GetFormRequest(
                $existingOrder->getReference(),
                $product,
                $campaign,
                $ajax,
                $existingOrder->getMerchant()->getId()
            )
        );
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
        $newUnshippedCart = $orderUpdateData->getUnshippedCart();

        if ($newShippedCart && !$this->areObjectsEqual($order->getShippedCart(), $newShippedCart)) {
            $order->setShippedCart($newShippedCart);
            $hasChanges = true;
        }


        if ($newUnshippedCart && !$this->areObjectsEqual($order->getUnshippedCart(), $newUnshippedCart)) {
            $order->setUnshippedCart($newUnshippedCart);
            $hasChanges = true;
        }

        $newInvoiceAddress = $orderUpdateData->getInvoiceAddress();
        if ($newInvoiceAddress && !$this->areObjectsEqual($order->getInvoiceAddress(), $newInvoiceAddress)) {
            $order->setInvoiceAddress($newInvoiceAddress);
            $hasChanges = true;
        }

        $newDeliveryAddress = $orderUpdateData->getDeliveryAddress();
        if ($newDeliveryAddress && !$this->areObjectsEqual($order->getDeliveryAddress(), $newDeliveryAddress)) {
            $order->setDeliveryAddress($newDeliveryAddress);
            $hasChanges = true;
        }

        if ($hasChanges) {
            $this->tryOrderUpdate($order);
        }

        return $order;
    }

    /**
     * Creates and saves a new SeQuraOrder.
     *
     * @param Webhook $webhook
     *
     * @return string
     *
     * @throws Exception
     */
    public function createOrder(Webhook $webhook): string
    {
        $seQuraOrder = $this->getSeQuraOrder($webhook->getOrderRef());

        $shopOrderReference = $this->shopOrderCreator->createOrder($seQuraOrder->getCartId());

        $updatedSeQuraOrder = (new CreateOrderRequest(
            OrderRequestStatusMapping::mapOrderRequestStatus($webhook->getSqState()),
            $seQuraOrder->getUnshippedCart(),
            $seQuraOrder->getDeliveryMethod(),
            $seQuraOrder->getCustomer(),
            $seQuraOrder->getPlatform(),
            $seQuraOrder->getDeliveryAddress(),
            $seQuraOrder->getInvoiceAddress(),
            $seQuraOrder->getGui(),
            $seQuraOrder->getMerchant(),
            MerchantReference::fromArray([
                'order_ref_1' => $shopOrderReference,
                'order_ref_2' => $webhook->getOrderRef()
            ])
        ))->toSequraOrderInstance($webhook->getOrderRef());

        $updatedSeQuraOrder->setPaymentMethod(
            $this->getOrderPaymentMethodInfo(
                $updatedSeQuraOrder->getReference(),
                $webhook->getProductCode(),
                (string)$updatedSeQuraOrder->getMerchant()->getId()
            )
        );

        // Update order with merchant order references so that core can update order state with all required data
        $this->orderRepository->setSeQuraOrder($updatedSeQuraOrder);

        return $shopOrderReference;
    }

    /**
     * Updates the SeQuraOrder status.
     *
     * @param Webhook $webhook
     *
     * @return void
     *
     * @throws InvalidOrderStateException|OrderNotFoundException
     */
    public function updateSeQuraOrderStatus(Webhook $webhook): void
    {
        $seQuraOrder = $this->getSeQuraOrder($webhook->getOrderRef());
        $seQuraOrder->setState(OrderRequestStatusMapping::mapOrderRequestStatus($webhook->getSqState()));
        $this->orderRepository->setSeQuraOrder($seQuraOrder);
    }

    /**
     * Returns webhook order reference.
     *
     * @param Webhook $webhook
     *
     * @return string
     *
     * @throws OrderNotFoundException
     */
    public function getOrderReference1(Webhook $webhook): string
    {
        $orderRef1 = $webhook->getOrderRef1();
        if (empty($orderRef1)) {
            $seQuraOrder = $this->getSeQuraOrder($webhook->getOrderRef());
            $orderRef1 = $seQuraOrder->getMerchantReference()->getOrderRef1();
        }

        return $orderRef1;
    }

    /**
     * @param SeQuraOrder $order
     *
     * @return void
     *
     * @throws HttpApiNotFoundException
     * @throws HttpRequestException
     * @throws Exception
     */
    protected function tryOrderUpdate(SeQuraOrder $order): void
    {
        try {
            $this->proxy->updateOrder($this->getUpdateOrderRequest($order));
        } catch (HttpApiNotFoundException $exception) {
            // Ignore not found errors for cancellation actions because SeQura returns
            // not found response for on-hold to cancel transitions (immediate cancelations from checkout)
            if (!in_array($order->getState(), [OrderRequestStates::CANCELLED, OrderRequestStates::ON_HOLD])) {
                throw $exception;
            }
        }

        $this->orderRepository->setSeQuraOrder($order);
    }

    /**
     * @param CreateOrderRequest $request
     *
     * @return SeQuraOrder|null
     */
    protected function getExistingOrderFor(CreateOrderRequest $request): ?SeQuraOrder
    {
        $existingOrder = null;
        if ($request->getCart()->getCartRef()) {
            $existingOrder = $this->orderRepository->getByCartId($request->getCart()->getCartRef());
        }

        if (!$existingOrder && $request->getMerchantReference()) {
            $existingOrder = $this->orderRepository->getByShopReference(
                $request->getMerchantReference()->getOrderRef1()
            );
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
    protected function getUpdateOrderRequest(SeQuraOrder $order): UpdateOrderRequest
    {
        return UpdateOrderRequest::fromArray([
            'merchant' => $order->getMerchant()->toArray(),
            'merchant_reference' => $order->getMerchantReference()->toArray(),
            'unshipped_cart' => $order->getUnshippedCart()->toArray(),
            'shipped_cart' => $order->getShippedCart()->toArray(),
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
     * @param mixed $object1
     * @param mixed $object2
     *
     * @return bool
     */
    protected function areObjectsEqual($object1, $object2): bool
    {
        if (method_exists($object1, 'toArray') && method_exists($object2, 'toArray')) {
            return json_encode($object1->toArray()) === json_encode($object2->toArray());
        }

        return json_encode($object1) === json_encode($object2);
    }

    /**
     * Gets the SeQura order.
     *
     * @param string $orderReference
     *
     * @return SeQuraOrder
     *
     * @throws OrderNotFoundException
     */
    private function getSeQuraOrder(string $orderReference): SeQuraOrder
    {
        $seQuraOrder = $this->orderRepository->getByOrderReference($orderReference);
        if (!$seQuraOrder) {
            throw new OrderNotFoundException("SeQura order with reference $orderReference is not found.", 404);
        }

        return $seQuraOrder;
    }

    /**
     * Returns PaymentMethod information for SeQura order.
     *
     * @param string $orderReference
     * @param string $paymentMethodId
     * @param string $merchantId
     *
     * @return PaymentMethod|null
     *
     * @throws HttpRequestException
     */
    private function getOrderPaymentMethodInfo(
        string $orderReference,
        string $paymentMethodId,
        string $merchantId
    ): ?PaymentMethod {
        $methodCategories = $this->getAvailablePaymentMethodsInCategories(
            $orderReference,
            $merchantId
        );

        foreach ($methodCategories as $category) {
            foreach ($category->getMethods() as $method) {
                if ($method->getProduct() === $paymentMethodId) {
                    $name = in_array($paymentMethodId, self::INSTALLMENT_METHOD_CODES) ?
                        $category->getTitle() :
                        $method->getTitle();
                    $icon = $method->getIcon() ?? '';
                    return new PaymentMethod($paymentMethodId, $name, $icon);
                }
            }
        }

        return null;
    }
}
