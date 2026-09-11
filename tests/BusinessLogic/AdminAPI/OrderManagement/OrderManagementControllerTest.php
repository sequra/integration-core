<?php

namespace SeQura\Core\Tests\BusinessLogic\AdminAPI\OrderManagement;

use SeQura\Core\BusinessLogic\Domain\Checkout\Services\CheckoutService;
use Exception;
use SeQura\Core\BusinessLogic\AdminAPI\AdminAPI;
use SeQura\Core\BusinessLogic\AdminAPI\OrderManagement\Requests\OrderUpdateRequest;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Address;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Cart;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Item\ProductItem;
use SeQura\Core\BusinessLogic\Domain\Order\Models\SeQuraOrder;
use SeQura\Core\BusinessLogic\Domain\Order\Service\OrderService;
use SeQura\Core\BusinessLogic\Domain\Order\ProxyContracts\OrderProxyInterface;
use SeQura\Core\BusinessLogic\Domain\Order\Builders\MerchantOrderRequestBuilder;
use SeQura\Core\BusinessLogic\Domain\Order\RepositoryContracts\SeQuraOrderRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\Order\OrderCreationInterface;
use SeQura\Core\Tests\BusinessLogic\AdminAPI\OrderManagement\MockComponents\MockOrderService;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class OrderManagementControllerTest
 *
 * @package SeQura\Core\Tests\BusinessLogic\AdminAPI\OrderManagement
 */
class OrderManagementControllerTest extends BaseTestCase
{
    /**
     * @var MockOrderService
     */
    private $orderService;

    public function setUp(): void
    {
        parent::setUp();

        $this->orderService = new MockOrderService(
            TestServiceRegister::getService(OrderProxyInterface::class),
            TestServiceRegister::getService(SeQuraOrderRepositoryInterface::class),
            TestServiceRegister::getService(MerchantOrderRequestBuilder::class),
            TestServiceRegister::getService(OrderCreationInterface::class),
            TestServiceRegister::getService(CheckoutService::class)
        );
        $this->orderService->setUpdatedOrder($this->seQuraOrder());

        TestServiceRegister::registerService(OrderService::class, function () {
            return $this->orderService;
        });
    }

    /**
     * @throws Exception
     */
    public function testUpdateIsSuccessful(): void
    {
        // Act
        $response = AdminAPI::get()->orderManagement('1')->updateOrder(
            new OrderUpdateRequest('ZXCV1234', $this->cart(1000), $this->cart(0))
        );

        // Assert
        self::assertTrue($response->isSuccessful());
    }

    /**
     * @throws Exception
     */
    public function testUpdateAnswersWithTheOrderSeQuraHolds(): void
    {
        // Arrange
        $order = $this->seQuraOrder();
        $order->setOrderRef1('ZXCV1234');
        $this->orderService->setUpdatedOrder($order);

        // Act
        $response = AdminAPI::get()->orderManagement('1')->updateOrder(
            new OrderUpdateRequest('ZXCV1234', $this->cart(1000), $this->cart(0))
        );

        // Assert
        self::assertEquals($order->toArray(), $response->toArray());
    }

    /**
     * @throws Exception
     */
    public function testUpdateSubmitsBothCarts(): void
    {
        // Act
        AdminAPI::get()->orderManagement('1')->updateOrder(
            new OrderUpdateRequest('ZXCV1234', $this->cart(1000), $this->cart(250))
        );

        // Assert
        $updateData = $this->orderService->getLastUpdateData();
        self::assertNotNull($updateData);
        self::assertEquals('ZXCV1234', $updateData->getOrderShopReference());
        self::assertNotNull($updateData->getShippedCart());
        self::assertNotNull($updateData->getUnshippedCart());
        self::assertEquals(1000, $updateData->getShippedCart()->getOrderTotalWithTax());
        self::assertEquals(250, $updateData->getUnshippedCart()->getOrderTotalWithTax());
        self::assertEquals('EUR', $updateData->getShippedCart()->getCurrency());
    }

    /**
     * @throws Exception
     */
    public function testUpdateKeepsTheItemsOfTheSubmittedCart(): void
    {
        // Act
        AdminAPI::get()->orderManagement('1')->updateOrder(
            new OrderUpdateRequest('ZXCV1234', $this->cart(1000), null)
        );

        // Assert
        $updateData = $this->orderService->getLastUpdateData();
        self::assertNotNull($updateData);
        self::assertNotNull($updateData->getShippedCart());
        $items = $updateData->getShippedCart()->getItems();
        self::assertCount(1, $items);
        self::assertEquals(1000, $items[0]->getTotalWithTax());
    }

    /**
     * @throws Exception
     */
    public function testUpdateLeavesAnOmittedCartAlone(): void
    {
        // Act
        AdminAPI::get()->orderManagement('1')->updateOrder(
            new OrderUpdateRequest('ZXCV1234', $this->cart(1000))
        );

        // Assert
        $updateData = $this->orderService->getLastUpdateData();
        self::assertNotNull($updateData);
        self::assertNull($updateData->getUnshippedCart());
    }

    /**
     * @throws Exception
     */
    public function testUpdateSubmitsBothAddresses(): void
    {
        // Act
        AdminAPI::get()->orderManagement('1')->updateOrder(new OrderUpdateRequest(
            'ZXCV1234',
            null,
            null,
            $this->address('Carrer del Rec'),
            $this->address('Gran Via')
        ));

        // Assert
        $updateData = $this->orderService->getLastUpdateData();
        self::assertNotNull($updateData);
        self::assertNotNull($updateData->getDeliveryAddress());
        self::assertNotNull($updateData->getInvoiceAddress());
        self::assertEquals('Carrer del Rec', $updateData->getDeliveryAddress()->getAddressLine1());
        self::assertEquals('Gran Via', $updateData->getInvoiceAddress()->getAddressLine1());
    }

    /**
     * @throws Exception
     */
    public function testUpdateLeavesAnOmittedAddressAlone(): void
    {
        // Act
        AdminAPI::get()->orderManagement('1')->updateOrder(
            new OrderUpdateRequest('ZXCV1234', $this->cart(1000), null, $this->address('Carrer del Rec'))
        );

        // Assert
        $updateData = $this->orderService->getLastUpdateData();
        self::assertNotNull($updateData);
        self::assertNotNull($updateData->getDeliveryAddress());
        self::assertNull($updateData->getInvoiceAddress());
    }

    /**
     * @throws Exception
     */
    public function testUpdateRunsInTheStoreTheFacadeWasGiven(): void
    {
        // Act
        AdminAPI::get()->orderManagement('7')->updateOrder(
            new OrderUpdateRequest('ZXCV1234', $this->cart(1000), $this->cart(0))
        );

        // Assert
        self::assertEquals('7', $this->orderService->getStoreIdOfLastUpdate());
    }

    /**
     * @throws Exception
     */
    public function testFailedUpdateAnswersUnsuccessfullyInsteadOfThrowing(): void
    {
        // Arrange
        $this->orderService->setUpdateException(new Exception('seQura does not know the order.'));

        // Act
        $response = AdminAPI::get()->orderManagement('1')->updateOrder(
            new OrderUpdateRequest('ZXCV1234', $this->cart(1000), $this->cart(0))
        );

        // Assert
        self::assertFalse($response->isSuccessful());
    }

    /**
     * Returns a cart of one product line worth the given total.
     *
     * @param int $totalWithTax
     *
     * @return Cart
     *
     * @throws Exception
     */
    private function address(string $addressLine1): Address
    {
        return new Address('', $addressLine1, '', '08003', 'Barcelona', 'ES');
    }

    /**
     * @param int $totalWithTax
     *
     * @return Cart
     *
     * @throws Exception
     */
    private function cart(int $totalWithTax): Cart
    {
        return new Cart('EUR', false, [
            new ProductItem('item-1', 'Item 1', $totalWithTax, 1, $totalWithTax, false),
        ]);
    }

    /**
     * @return SeQuraOrder
     *
     * @throws Exception
     */
    private function seQuraOrder(): SeQuraOrder
    {
        $order = file_get_contents(__DIR__ . '/../../Common/MockObjects/SeQuraOrder.json');

        return SeQuraOrder::fromArray(json_decode($order, true)['order']);
    }
}
