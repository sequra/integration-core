<?php

namespace SeQura\Core\BusinessLogic\Domain\Order\ProxyContracts;

use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\CreateOrderRequest;
use SeQura\Core\BusinessLogic\Domain\Order\Models\GetAvailablePaymentMethodsRequest;
use SeQura\Core\BusinessLogic\Domain\Order\Models\GetFormRequest;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\UpdateOrderRequest;
use SeQura\Core\BusinessLogic\Domain\Order\Models\SeQuraForm;
use SeQura\Core\BusinessLogic\Domain\Order\Models\SeQuraOrder;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Models\SeQuraPaymentMethod;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Models\SeQuraPaymentMethodCategory;
use SeQura\Core\Infrastructure\Http\Exceptions\HttpRequestException;

/**
 * Class OrderProxyInterface
 *
 * @package SeQura\Core\BusinessLogic\Domain\Order\ProxyContracts
 */
interface OrderProxyInterface
{
    /**
     * Get all available payment methods from SeQura for the provided order.
     *
     * @param GetAvailablePaymentMethodsRequest $request
     *
     * @throws HttpRequestException
     *
     * @return SeQuraPaymentMethod[]
     */
    public function getAvailablePaymentMethods(GetAvailablePaymentMethodsRequest $request): array;

    /**
     * Get all available payment methods in categories from SeQura for the provided order.
     *
     * @param GetAvailablePaymentMethodsRequest $request
     *
     * @throws HttpRequestException
     *
     * @return SeQuraPaymentMethodCategory[]
     */
    public function getAvailablePaymentMethodsInCategories(GetAvailablePaymentMethodsRequest $request): array;

    /**
     * Creates a new order.
     *
     * @param CreateOrderRequest $request
     *
     * @throws HttpRequestException
     *
     * @return SeQuraOrder
     */
    public function createOrder(CreateOrderRequest $request): SeQuraOrder;

    /**
     * Acknowledges an order on the SeQura API.
     *
     * @param string $id
     * @param CreateOrderRequest $request
     *
     * @return SeQuraOrder Returns acknowledged SeQuraOrder instance if the operation has been successful.
     *
     * @throws HttpRequestException
     */
    public function acknowledgeOrder(string $id, CreateOrderRequest $request): SeQuraOrder;

    /**
     * Updates an existing order on the SeQura API.
     *
     * @param UpdateOrderRequest $request
     *
     * @return boolean Whether the update operation has been successful or not.
     *
     * @throws HttpRequestException
     */
    public function updateOrder(UpdateOrderRequest $request): bool;

    /**
     * Gets the user verification form.
     *
     * @param GetFormRequest $request
     *
     * @throws HttpRequestException
     *
     * @return SeQuraForm
     */
    public function getForm(GetFormRequest $request): SeQuraForm;
}
