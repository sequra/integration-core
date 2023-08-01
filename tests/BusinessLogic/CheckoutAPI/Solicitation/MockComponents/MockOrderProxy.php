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
     * @var SeQuraForm|null
     */
    private $form;
    /**
     * @var GetFormRequest
     */
    private $lastGetFormRequest;

    /**
     * @param ?SeQuraOrder $order
     * @param array $availablePaymentMethods
     * @param SeQuraForm|null $form
     * @return void
     */
    public function setMockResult(?SeQuraOrder $order, array $availablePaymentMethods = [], SeQuraForm $form = null): void
    {
        $this->order = $order;
        $this->availablePaymentMethods = $availablePaymentMethods;
        $this->form = $form ?? new SeQuraForm('');
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

    public function acknowledgeOrder(string $id, CreateOrderRequest $request): SeQuraOrder
    {
        return $this->order;
    }

    public function updateOrder(UpdateOrderRequest $request): bool
    {
        return true;
    }

    public function getForm(GetFormRequest $request): SeQuraForm
    {
        $this->lastGetFormRequest = $request;
        return $this->form;
    }

    /**
     * @return GetFormRequest
     */
    public function getLastGetFormRequest(): GetFormRequest
    {
        return $this->lastGetFormRequest;
    }
}
