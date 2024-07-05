<?php

namespace SeQura\Core\Tests\BusinessLogic\WebhookAPI\MockComponents;

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
use SeQura\Core\BusinessLogic\Domain\Webhook\Models\Webhook;
use SeQura\Core\BusinessLogic\Webhook\Services\ShopOrderService;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class MockShopOrderService
 *
 * @package SeQura\Core\Tests\BusinessLogic\WebhookAPI\MockComponents
 */
class MockShopOrderService implements ShopOrderService
{
    public $reportOrderIds = [];
    public $statisticsOrderIds = [];

    /**
     * @inheritDoc
     */
    public function updateStatus(
        Webhook $webhook,
        string $status,
        ?int $reasonCode = null,
        ?string $message = null
    ): void {
    }

    /**
     * @inheritDoc
     */
    public function getReportOrderIds(int $page, int $limit = 5000): array
    {
        return array_slice($this->reportOrderIds, $page * $limit, $limit);
    }

    /**
     * @inheritDoc
     */
    public function getStatisticsOrderIds(int $page, int $limit = 5000): array
    {
        return array_slice($this->statisticsOrderIds, $page * $limit, $limit);
    }

    /**
     * @inheritDoc
     */
    public function getOrderUrl(string $merchantReference): string
    {
        return 'https.test.url/' . $merchantReference;
    }

    /**
     * @throws InvalidCartItemsException
     * @throws InvalidGuiLayoutValueException
     */
    public function getCreateOrderRequest(string $orderReference): CreateOrderRequest
    {
        $merchant = new Merchant('testMerchantId');
        $merchantReference = new MerchantReference('test123');
        $cart = new Cart('testCurrency', false, [
            new ProductItem('testItemReference','testName', 5,2, 10, false)
        ], $orderReference);

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

        $customer = new Customer('test@test.test','testCode','testIpNum','testAgent');
        $platform = new Platform('testName','testVersion','testUName','testDbName','testDbVersion');
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
