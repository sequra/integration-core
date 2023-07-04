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
use SeQura\Core\BusinessLogic\SeQuraAPI\Order\Requests\UpdateOrderCartsHttpRequest;
use SeQura\Core\BusinessLogic\SeQuraAPI\Order\Requests\UpdateOrderHttpRequest;

/**
 * Class OrderProxy
 *
 * @package SeQura\Core\BusinessLogic\SeQuraAPI\Order
 */
class OrderProxy extends AuthorizedProxy implements OrderProxyInterface
{
    private const PAYMENT_OPTIONS_KEY = 'payment_options';
    private const METHODS_KEY = 'methods';

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

        return $this->generateSeQuraOrder($this->getOrderUUID($response->getHeaders()),$request);
    }

    /**
     * @inheritDoc
     */
    public function updateOrder(string $id, CreateOrderRequest $request): bool
    {
        return $this->put(new UpdateOrderHttpRequest($id, $request))->isSuccessful();
    }

    /**
     * @inheritDoc
     */
    public function updateOrderCarts(string $id, UpdateOrderRequest $request): bool
    {
        return $this->put(new UpdateOrderCartsHttpRequest($id, $request))->isSuccessful();
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
    private function getOrderUUID(array $headers): string
    {
        $location = array_key_exists('location', $headers) ? $headers['location'] : '';

        return !empty($location) ? basename(parse_url($location, PHP_URL_PATH)) : '';
    }

    /**
     * Creates a SeQuraOrder instance from request and response data.
     *
     * @param string $id
     * @param CreateOrderRequest $request
     *
     * @return SeQuraOrder
     */
    private function generateSeQuraOrder(string $id, CreateOrderRequest $request): SeQuraOrder
    {
        $order = (new SeQuraOrder())
            ->setReference($id)
            ->setState($request->getState())
            ->setMerchant($request->getMerchant())
            ->setMerchantReference($request->getMerchantReference())
            ->setCart($request->getCart())
            ->setDeliveryMethod($request->getDeliveryMethod())
            ->setDeliveryAddress($request->getDeliveryAddress())
            ->setInvoiceAddress($request->getInvoiceAddress())
            ->setCustomer($request->getCustomer())
            ->setPlatform($request->getPlatform())
            ->setGui($request->getGui());

        if($request->getCart()->getCartRef()) {
            $order->setCartId($request->getCart()->getCartRef());
        }

        if($request->getMerchantReference()){
            $order->setOrderRef1($request->getMerchantReference()->getOrderRef1());
        }

        return $order;
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
    private function getListOfPaymentMethods(array $responseData): array
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
    private function getListOfPaymentMethodsInCategories(array $responseData): array
    {
        $paymentMethodCategories = [];

        foreach ($responseData[self::PAYMENT_OPTIONS_KEY] as $category) {
            $paymentMethodCategories[] = SeQuraPaymentMethodCategory::fromArray($category);
        }

        return $paymentMethodCategories;
    }
}
