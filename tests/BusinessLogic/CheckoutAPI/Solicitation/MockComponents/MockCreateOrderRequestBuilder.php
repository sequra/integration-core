<?php

namespace SeQura\Core\Tests\BusinessLogic\CheckoutAPI\Solicitation\MockComponents;

use SeQura\Core\BusinessLogic\Domain\Order\Builders\CreateOrderRequestBuilder;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidCartItemsException;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidGuiLayoutValueException;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Address;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Cart;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\CreateOrderRequest;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Customer;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\DeliveryMethod;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Gui;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Item\ProductItem;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Merchant;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\MerchantReference;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Platform;

/**
 * Class TestCreateOrderRequestBuilder
 *
 * @package BusinessLogic\CheckoutAPI\Solicitation\MockComponents
 */
class MockCreateOrderRequestBuilder implements CreateOrderRequestBuilder
{
    /**
     * @var \Exception|null
     */
    private $throwException;
    /**
     * @var string
     */
    private $cartId;

    /**
     * @var CreateOrderRequest $orderRequest
     */
    private $orderRequest;

    public function __construct(\Exception $exception = null, $cartId = 'testCart123')
    {
        $this->throwException = $exception;
        $this->cartId = $cartId;
    }

    /**
     * @return CreateOrderRequest
     *
     * @throws InvalidCartItemsException
     * @throws InvalidGuiLayoutValueException
     */
    public function build(): CreateOrderRequest
    {
        if ($this->throwException) {
            throw $this->throwException;
        }

        if ($this->orderRequest) {
            return $this->orderRequest;
        }

        return $this->generateMinimalCreateOrderRequest();
    }

    /**
     * @param CreateOrderRequest $mockCreateOrderRequest
     *
     * @return void
     */
    public function setMockOrderRequest(CreateOrderRequest $mockCreateOrderRequest): void
    {
        $this->orderRequest = $mockCreateOrderRequest;
    }

    /**
     * @return CreateOrderRequest
     *
     * @throws InvalidCartItemsException
     * @throws InvalidGuiLayoutValueException
     */
    private function generateMinimalCreateOrderRequest(): CreateOrderRequest
    {
        $merchant = new Merchant('testMerchantId');
        $merchantReference = new MerchantReference('test123');
        $cart = new Cart('testCurrency', false, [
            new ProductItem('testItemReference', 'testName', 5, 2, 10, false)
        ], $this->cartId);

        $deliveryMethod = new DeliveryMethod('testDeliveryMethodName');
        $deliveryAddress = new Address(
            'testDeliveryAddressCompany',
            'testDeliveryAddressLine1',
            'testDeliveryAddressLine2',
            'testDeliveryAddressPostalCode',
            'testDeliveryAddressCity',
            'ES'
        );

        $invoiceAddress = new Address(
            'testInvoiceAddressCompany',
            'testInvoiceAddressLine1',
            'testInvoiceAddressLine2',
            'testInvoiceAddressPostalCode',
            'testInvoiceAddressCity',
            'ES'
        );

        $customer = new Customer('test@test.test', 'testCode', 'testIpNum', 'testAgent');
        $platform = new Platform('testName', 'testVersion', 'testUName', 'testDbName', 'testDbVersion');
        $gui = new Gui(Gui::ALLOWED_VALUES['desktop']);

        return new CreateOrderRequest(
            'testState',
            $cart,
            $deliveryMethod,
            $customer,
            $platform,
            $deliveryAddress,
            $invoiceAddress,
            $gui,
            $merchant,
            $merchantReference
        );
    }
}
