<?php

namespace SeQura\Core\BusinessLogic\SeQuraAPI\Order;

use Exception;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\CreateOrderRequest;
use SeQura\Core\BusinessLogic\Domain\Order\Models\GetAvailablePaymentMethodsRequest;
use SeQura\Core\BusinessLogic\Domain\Order\Models\GetFormRequest;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\UpdateOrderRequest;
use SeQura\Core\BusinessLogic\Domain\Order\Models\SeQuraForm;
use SeQura\Core\BusinessLogic\Domain\Order\Models\SeQuraOrder;
use SeQura\Core\BusinessLogic\Domain\Order\ProxyContracts\OrderProxyInterface;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Models\SeQuraPaymentMethod;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Models\SeQuraPaymentMethodCategory;
use SeQura\Core\BusinessLogic\SeQuraAPI\Authorization\AuthorizedProxy;
use SeQura\Core\BusinessLogic\SeQuraAPI\Order\Requests\CreateOrderHttpRequest;
use SeQura\Core\BusinessLogic\SeQuraAPI\Order\Requests\GetAvailablePaymentMethodsHttpRequest;
use SeQura\Core\BusinessLogic\SeQuraAPI\Order\Requests\GetFormHttpRequest;
use SeQura\Core\BusinessLogic\SeQuraAPI\Order\Requests\AcknowledgeOrderHttpRequest;
use SeQura\Core\BusinessLogic\SeQuraAPI\Order\Requests\UpdateOrderHttpRequest;

/**
 * Class OrderProxy
 *
 * @package SeQura\Core\BusinessLogic\SeQuraAPI\Order
 */
class OrderProxy extends AuthorizedProxy implements OrderProxyInterface
{
    protected const PAYMENT_OPTIONS_KEY = 'payment_options';
    protected const METHODS_KEY = 'methods';

    /**
     * @inheritDoc
     *
     * @throws Exception
     */
    public function getAvailablePaymentMethods(GetAvailablePaymentMethodsRequest $request): array
    {
        $response = $this->get(new GetAvailablePaymentMethodsHttpRequest($request))->decodeBodyToArray();

        return $this->getListOfPaymentMethods($response);
    }

    /**
     * @inheritDoc
     *
     * @throws Exception
     */
    public function getAvailablePaymentMethodsInCategories(GetAvailablePaymentMethodsRequest $request): array
    {
        $response = $this->get(new GetAvailablePaymentMethodsHttpRequest($request))->decodeBodyToArray();

        return $this->getListOfPaymentMethodsInCategories($response);
    }

    /**
     * @inheritDoc
     */
    public function createOrder(CreateOrderRequest $request): SeQuraOrder
    {
        $response = $this->post(new CreateOrderHttpRequest($request));

        return $request->toSequraOrderInstance($this->getOrderUUID($response->getHeaders()));
    }

    /**
     * @inheritDoc
     */
    public function acknowledgeOrder(string $id, CreateOrderRequest $request): SeQuraOrder
    {
        $this->put(new AcknowledgeOrderHttpRequest($id, $request));

        return $request->toSequraOrderInstance($id);
    }

    /**
     * @inheritDoc
     *
     * @noinspection NullPointerExceptionInspection
     */
    public function updateOrder(UpdateOrderRequest $request): bool
    {
        return $this->put(new UpdateOrderHttpRequest(
            $request->getMerchant()->getId(),
            $request->getMerchantReference()->getOrderRef1(),
            $request
        ))->isSuccessful();
    }

    /**
     * @inheritDoc
     */
    public function getForm(GetFormRequest $request): SeQuraForm
    {
        $response = $this->get(new GetFormHttpRequest($request));

        return new SeQuraForm($response->getBody());
    }

    /**
     * Retrieves SeQura's order ID from the location header.
     *
     * @param array $headers
     *
     * @return string
     */
    protected function getOrderUUID(array $headers): string
    {
        $headers = array_change_key_case($headers);
        $location = array_key_exists('location', $headers) ? $headers['location'] : '';

        return !empty($location) ? basename(parse_url($location, PHP_URL_PATH)) : '';
    }

    /**
     * Gets a list of SeQuraPaymentMethods from the raw response data.
     *
     * @param array $responseData
     *
     * @return SeQuraPaymentMethod[]
     *
     * @throws Exception
     */
    protected function getListOfPaymentMethods(array $responseData): array
    {
        $paymentMethods = [];

        foreach ($responseData[self::PAYMENT_OPTIONS_KEY] as $option) {
            foreach ($option[self::METHODS_KEY] as $method) {
                $paymentMethods[] = SeQuraPaymentMethod::fromArray($method);
            }
        }

        return $paymentMethods;
    }

    /**
     * Gets a list of SeQuraPaymentMethodCategories from the raw response data.
     *
     * @param array $responseData
     *
     * @return SeQuraPaymentMethodCategory[]
     *
     * @throws Exception
     */
    protected function getListOfPaymentMethodsInCategories(array $responseData): array
    {
        $paymentMethodCategories = [];

        foreach ($responseData[self::PAYMENT_OPTIONS_KEY] as $category) {
            $paymentMethodCategories[] = SeQuraPaymentMethodCategory::fromArray($category);
        }

        return $paymentMethodCategories;
    }
}
