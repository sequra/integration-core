<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\Order\Services;

use DateTime;
use Exception;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
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
use SeQura\Core\BusinessLogic\Domain\Order\Models\SeQuraOrder;
use SeQura\Core\BusinessLogic\Domain\Order\RepositoryContracts\SeQuraOrderRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Order\Service\OrderService;
use SeQura\Core\Infrastructure\Http\HttpClient;
use SeQura\Core\Infrastructure\Http\HttpResponse;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TestHttpClient;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

class OrderServiceTest extends BaseTestCase
{
    /**
     * @var OrderService
     */
    private $orderService;

    /**
     * @var SeQuraOrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var TestHttpClient
     */
    public $httpClient;

    public function setUp(): void
    {
        parent::setUp();

        $httpClient = TestServiceRegister::getService(HttpClient::class);
        $this->httpClient = $httpClient;
        TestServiceRegister::registerService(HttpClient::class, static function () use ($httpClient) {
            return $httpClient;
        });

        $this->orderService = TestServiceRegister::getService(OrderService::class);
        $this->orderRepository = TestServiceRegister::getService(SeQuraOrderRepositoryInterface::class);
    }

    /**
     * @throws Exception
     */
    public function testGetOrderByShopReference(): void
    {
        // Arrange
        $order = file_get_contents(__DIR__ . '/../../../Common/MockObjects/SeQuraOrder.json');
        $array = json_decode($order, true);
        $seQuraOrder = SeQuraOrder::fromArray($array['order']);
        $seQuraOrder->setReference('sequra-ref-1234');
        $seQuraOrder->setCartId('5678');
        $seQuraOrder->setOrderRef1('ZXCV1234');
        $seQuraOrder->setState('approved');

        StoreContext::doWithStore('1', [$this->orderRepository, 'setSeQuraOrder'], [$seQuraOrder]);

        // Act
        $response = $this->orderService->getOrderByShopReference('ZXCV1234');

        // Assert
        self::assertEquals($seQuraOrder->toArray(), $response->toArray());
    }

    /**
     * @throws Exception
     */
    public function testGetOrderBatchByShopReferences(): void
    {
        // Arrange
        $orderRefs = ['1', '2', '3', '4', '5', '6', '7'];

        foreach ($orderRefs as $ref) {
            $order = file_get_contents(__DIR__ . '/../../../Common/MockObjects/SeQuraOrder.json');
            $array = json_decode($order, true);
            $seQuraOrder = SeQuraOrder::fromArray($array['order']);
            $seQuraOrder->setReference($ref);
            $seQuraOrder->setCartId('cart-' . $ref);
            $seQuraOrder->setOrderRef1('shop-' . $ref);
            $seQuraOrder->setState('approved');

            StoreContext::doWithStore('1', [$this->orderRepository, 'setSeQuraOrder'], [$seQuraOrder]);
        }

        // Act
        $shopOrderRefs = ['shop-1', 'shop-4', 'shop-5', 'shop-7'];
        $response = $this->orderService->getOrderBatchForShopReferences($shopOrderRefs);

        // Assert
        self::assertCount(4, $response);
        foreach ($shopOrderRefs as $index => $ref) {
            self::assertEquals($ref, $response[$index]->getOrderRef1());
        }
    }

    /**
     * @throws Exception
     */
    public function testIsUpdateResponseSuccessful(): void
    {
        // Arrange
        $this->httpClient->setMockResponses([new HttpResponse(204, ['UUID' => 'testUUID'], ''),
            new HttpResponse(204, ['UUID' => 'testUUID'], ''),
            new HttpResponse(204, ['UUID' => 'testUUID'], ''),
            new HttpResponse(204, ['UUID' => 'testUUID'], '')]);

        $order = file_get_contents(__DIR__ . '/../../../Common/MockObjects/SeQuraOrder.json');
        $array = json_decode($order, true);
        $seQuraOrder = SeQuraOrder::fromArray($array['order']);
        $seQuraOrder->setReference('sequra-ref-1234');
        $seQuraOrder->setCartId('5678');
        $seQuraOrder->setOrderRef1('ZXCV1234');
        $seQuraOrder->setState('approved');

        StoreContext::doWithStore('1', [$this->orderRepository, 'setSeQuraOrder'], [$seQuraOrder]);

        $orderUpdateData = $this->getOrderUpdateData();

        // Act
        $response = $this->orderService->updateOrder($orderUpdateData);

        // Assert
        self::assertEquals($this->expectedShippedCartToArrayResponse(), $response->getShippedCart()->toArray());
        self::assertEquals($this->expectedUnshippedToArrayResponse(), $response->getUnshippedCart()->toArray());
        self::assertEquals($this->expectedDeliveryAddressToArrayResponse(), $response->getDeliveryAddress()->toArray());
        self::assertEquals($this->expectedInvoiceAddressToArrayResponse(), $response->getInvoiceAddress()->toArray());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testGetPaymentMethodsInCategoriesSuccessfulResponse(): void
    {
        $rawResponseBody = file_get_contents(
            __DIR__ . '/../../../Common/ApiResponses/Order/GetPaymentMethodsResponses/SuccessfulResponse.json'
        );

        $this->httpClient->setMockResponses([new HttpResponse(200, [], $rawResponseBody)]);
        $response = $this->orderService->getAvailablePaymentMethodsInCategories('testId');
        $responseBody = json_decode($rawResponseBody, true);
        $paymentMethodCategories = [];

        foreach ($responseBody['payment_options'] as $category) {
            $paymentMethodCategories[] = $category;
        }

        for ($i = 0, $iMax = count($paymentMethodCategories); $i < $iMax; $i++) {
            self::assertEquals($paymentMethodCategories[$i]['title'], $response[$i]->getTitle());
            self::assertEquals($paymentMethodCategories[$i]['description'], $response[$i]->getDescription());
            self::assertEquals($paymentMethodCategories[$i]['icon'], $response[$i]->getIcon());

            for ($j = 0, $jMax = count($paymentMethodCategories[$i]['methods']); $j < $jMax; $j++) {
                self::assertEquals($paymentMethodCategories[$i]['methods'][$j]['product'], $response[$i]->getMethods()[$j]->getProduct());
                self::assertEquals($paymentMethodCategories[$i]['methods'][$j]['campaign'], $response[$i]->getMethods()[$j]->getCampaign());
                self::assertEquals($paymentMethodCategories[$i]['methods'][$j]['title'], $response[$i]->getMethods()[$j]->getTitle());
                self::assertEquals($paymentMethodCategories[$i]['methods'][$j]['long_title'], $response[$i]->getMethods()[$j]->getLongTitle());
                self::assertEquals($paymentMethodCategories[$i]['methods'][$j]['claim'], $response[$i]->getMethods()[$j]->getClaim());
                self::assertEquals($paymentMethodCategories[$i]['methods'][$j]['description'], $response[$i]->getMethods()[$j]->getDescription());
                self::assertEquals($paymentMethodCategories[$i]['methods'][$j]['icon'], $response[$i]->getMethods()[$j]->getIcon());
                self::assertEquals(new DateTime($paymentMethodCategories[$i]['methods'][$j]['starts_at']), $response[$i]->getMethods()[$j]->getStartsAt());
                self::assertEquals(new DateTime($paymentMethodCategories[$i]['methods'][$j]['ends_at']), $response[$i]->getMethods()[$j]->getEndsAt());
                self::assertEquals($paymentMethodCategories[$i]['methods'][$j]['min_amount'] ?? null, $response[$i]->getMethods()[$j]->getMinAmount());
                self::assertEquals($paymentMethodCategories[$i]['methods'][$j]['max_amount'] ?? null, $response[$i]->getMethods()[$j]->getMaxAmount());
                self::assertEquals($paymentMethodCategories[$i]['methods'][$j]['cost_description'], $response[$i]->getMethods()[$j]->getCostDescription());
                self::assertEquals($paymentMethodCategories[$i]['methods'][$j]['cost']['setup_fee'], $response[$i]->getMethods()[$j]->getCost()->getSetupFee());
                self::assertEquals($paymentMethodCategories[$i]['methods'][$j]['cost']['instalment_fee'], $response[$i]->getMethods()[$j]->getCost()->getInstalmentFee());
                self::assertEquals($paymentMethodCategories[$i]['methods'][$j]['cost']['down_payment_fees'], $response[$i]->getMethods()[$j]->getCost()->getDownPaymentFees());
                self::assertEquals($paymentMethodCategories[$i]['methods'][$j]['cost']['instalment_total'], $response[$i]->getMethods()[$j]->getCost()->getInstalmentTotal());
            }
        }
    }

    /**
     * Returns OrderUpdateData example.
     *
     * @return OrderUpdateData
     *
     * @throws InvalidCartItemsException
     * @throws InvalidQuantityException
     */
    private function getOrderUpdateData(): OrderUpdateData
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

    /**
     * @return array
     */
    private function expectedShippedCartToArrayResponse(): array
    {
        return [
            "currency" => "EUR",
            "gift" => false,
            "order_total_with_tax" => 10,
            "items" => [
                [
                    "type" => "product",
                    "total_with_tax" => 10,
                    "reference" => "testItemReference2",
                    "name" => "testName2",
                    "price_with_tax" => 5,
                    "quantity" => 2,
                    "downloadable" => false,
                    "perishable" => true,
                    "personalized" => true,
                    "restockable" => true,
                    "category" => "testCategory",
                    "description" => "testDescription",
                    "manufacturer" => "testManufacturer",
                    "supplier" => "testSupplier",
                    "product_id" => "testProductId",
                    "url" => "testUrl",
                    "tracking_reference" => "testTrackingReference"
                ]
            ]
        ];
    }

    private function expectedUnshippedToArrayResponse(): array
    {
        return [
            "currency" => "EUR",
            "gift" => false,
            "order_total_with_tax" => 20,
            "cart_ref" => 'testCartRef',
            "created_at" => 'testCreatedAt',
            "updated_at" => 'testUpdatedAt',
            "items" => [
                [
                    "type" => "product",
                    "total_with_tax" => 10,
                    "reference" => "testItemReference1",
                    "name" => "testName1",
                    "price_with_tax" => 5,
                    "quantity" => 2,
                    "downloadable" => false,
                    "perishable" => true,
                    "personalized" => true,
                    "restockable" => true,
                    "category" => "testCategory",
                    "description" => "testDescription",
                    "manufacturer" => "testManufacturer",
                    "supplier" => "testSupplier",
                    "product_id" => "testProductId",
                    "url" => "testUrl",
                    "tracking_reference" => "testTrackingReference"
                ],
                [
                    "type" => "handling",
                    "reference" => "testItemReference4",
                    "name" => "testName4",
                    "total_with_tax" => 5
                ],
                [
                    "type" => "invoice_fee",
                    "total_with_tax" => 30
                ],
                [
                    "type" => "discount",
                    "reference" => "testItemReference5",
                    "name" => "testName5",
                    "total_with_tax" => -20
                ],
                [
                    "type" => "other_payment",
                    "reference" => "testItemReference3",
                    "name" => "testName3",
                    "total_with_tax" => -5
                ]
            ]
        ];
    }

    private function expectedDeliveryAddressToArrayResponse(): array
    {
        return [
            "given_names" => "testDeliveryAddressGivenNames",
            "surnames" => "testDeliveryAddressSurnames",
            "company" => "testDeliveryAddressCompany",
            "address_line_1" => "testDeliveryAddressLine1",
            "address_line_2" => "testDeliveryAddressLine2",
            "postal_code" => "testDeliveryAddressPostalCode",
            "city" => "testDeliveryAddressCity",
            "country_code" => "ES",
            "phone" => "testDeliveryAddressPhone",
            "mobile_phone" => "testDeliveryAddressMobilePhone",
            "state" => "testDeliveryAddressState",
            "extra" => "testDeliveryAddressExtra",
            "vat_number" => "testDeliveryAddressVatNumber"
        ];
    }

    private function expectedInvoiceAddressToArrayResponse(): array
    {
        return [
            "given_names" => "testInvoiceAddressGivenNames",
            "surnames" => "testInvoiceAddressSurnames",
            "company" => "testInvoiceAddressCompany",
            "address_line_1" => "testInvoiceAddressLine1",
            "address_line_2" => "testInvoiceAddressLine2",
            "postal_code" => "testInvoiceAddressPostalCode",
            "city" => "testInvoiceAddressCity",
            "country_code" => "ES",
            "phone" => "testInvoiceAddressPhone",
            "mobile_phone" => "testInvoiceAddressMobilePhone",
            "state" => "testInvoiceAddressState",
            "extra" => "testInvoiceAddressExtra",
            "vat_number" => "testInvoiceAddressVatNumber"
        ];
    }
}
