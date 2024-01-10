<?php

namespace SeQura\Core\Tests\BusinessLogic\TransactionLog\Mocks;

use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidCartItemsException;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidQuantityException;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Address;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Cart;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Item\DiscountItem;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Item\HandlingItem;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Item\InvoiceFeeItem;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Item\OtherPaymentItem;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Item\ProductItem;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderUpdateData;

/**
 * Class MockOrderUpdateData
 *
 * @package SeQura\Core\Tests\BusinessLogic\TransactionLog\Mocks
 */
class MockOrderUpdateData
{
    /**
     * Returns OrderUpdateData example.
     *
     * @return OrderUpdateData
     *
     * @throws InvalidCartItemsException
     * @throws InvalidQuantityException
     */
    public static function getOrderUpdateData(): OrderUpdateData
    {
        $unshippedCart = new Cart(
            'EUR',
            false,
            [
                new ProductItem(
                    'testItemReference1',
                    'testName1',
                    5,
                    2,
                    10,
                    false,
                    true,
                    true,
                    true,
                    'testCategory',
                    'testDescription',
                    'testManufacturer',
                    'testSupplier',
                    'testProductId',
                    'testUrl',
                    'testTrackingReference'
                ),
                new HandlingItem('testItemReference4', 'testName4', 5),
                new InvoiceFeeItem(30),
                new DiscountItem('testItemReference5', 'testName5', -20),
                new OtherPaymentItem('testItemReference3', 'testName3', -5)
            ],
            'testCartRef',
            'testCreatedAt',
            'testUpdatedAt'
        );

        $shippedCart = new Cart('EUR', false, [
            new ProductItem(
                'testItemReference2',
                'testName2',
                5,
                2,
                10,
                false,
                true,
                true,
                true,
                'testCategory',
                'testDescription',
                'testManufacturer',
                'testSupplier',
                'testProductId',
                'testUrl',
                'testTrackingReference'
            ),
        ]);
        $deliveryAddress = new Address(
            'testDeliveryAddressCompany',
            'testDeliveryAddressLine1',
            'testDeliveryAddressLine2',
            'testDeliveryAddressPostalCode',
            'testDeliveryAddressCity',
            'ES',
            'testDeliveryAddressGivenNames',
            'testDeliveryAddressSurnames',
            'testDeliveryAddressPhone',
            'testDeliveryAddressMobilePhone',
            'testDeliveryAddressState',
            'testDeliveryAddressExtra',
            'testDeliveryAddressVatNumber'
        );

        $invoiceAddress = new Address(
            'testInvoiceAddressCompany',
            'testInvoiceAddressLine1',
            'testInvoiceAddressLine2',
            'testInvoiceAddressPostalCode',
            'testInvoiceAddressCity',
            'ES',
            'testInvoiceAddressGivenNames',
            'testInvoiceAddressSurnames',
            'testInvoiceAddressPhone',
            'testInvoiceAddressMobilePhone',
            'testInvoiceAddressState',
            'testInvoiceAddressExtra',
            'testInvoiceAddressVatNumber'
        );

        return new OrderUpdateData('ZXCV1234', $shippedCart, $unshippedCart, $deliveryAddress, $invoiceAddress);
    }
}
