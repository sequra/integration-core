<?php

namespace SeQura\Core\Tests\BusinessLogic\CheckoutAPI\Solicitation\MockComponents;

use SeQura\Core\BusinessLogic\Domain\Order\Models\GetAvailablePaymentMethodsRequest;
use SeQura\Core\BusinessLogic\Domain\Order\Models\GetFormRequest;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\CreateOrderRequest;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\UpdateOrderRequest;
use SeQura\Core\BusinessLogic\Domain\Order\Models\SeQuraForm;
use SeQura\Core\BusinessLogic\Domain\Order\Models\SeQuraOrder;
use SeQura\Core\BusinessLogic\Domain\Order\ProxyContracts\OrderProxyInterface;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Models\SeQuraPaymentMethod;

/**
 * Interface MockOrderProxy
 *
 * @package SeQura\Core\Tests\BusinessLogic\CheckoutAPI\Solicitation\MockComponents
 */
class MockOrderProxy implements OrderProxyInterface
{
    /**
     * @var SeQuraOrder|null
     */
    private $order;
    /**
     * @var SeQuraPaymentMethod[]
     */
    private $availablePaymentMethods;

    /**
     * @param ?SeQuraOrder $order
     * @return void
     */
    public function setMockResult(?SeQuraOrder $order, array $availablePaymentMethods = []): void
    {
        $this->order = $order;
        $this->availablePaymentMethods = $availablePaymentMethods;
    }
    public function getAvailablePaymentMethods(GetAvailablePaymentMethodsRequest $request): array
    {
        return $this->availablePaymentMethods;
    }

    public function getAvailablePaymentMethodsInCategories(GetAvailablePaymentMethodsRequest $request): array
    {
        return [];
    }

    public function createOrder(CreateOrderRequest $request): SeQuraOrder
    {
        return $this->order;
    }

    public function updateOrder(string $id, CreateOrderRequest $request): SeQuraOrder
    {
        return $this->order;
    }

    public function updateOrderCarts(string $id, UpdateOrderRequest $request): bool
    {
        return true;
    }

    public function getForm(GetFormRequest $request): SeQuraForm
    {
        return new SeQuraForm('test');
    }
}
