<?php

namespace SeQura\Core\Tests\BusinessLogic\CheckoutAPI\Solicitation\MockComponents;

use SeQura\Core\BusinessLogic\Domain\Order\Builders\CreateOrderRequestBuilder;
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

    public function __construct(\Exception $exception = null, $cartId = 'testCart123')
    {
        $this->throwException = $exception;
        $this->cartId = $cartId;
    }

    public function build(): CreateOrderRequest
    {
        if ($this->throwException) {
            throw $this->throwException;
        }

        return $this->generateMinimalCreateOrderRequest();
    }

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
            $merchant,
            $cart,
            $deliveryMethod,
            $customer,
            $platform,
            $deliveryAddress,
            $invoiceAddress,
            $gui,
            $merchantReference
        );
    }
}
