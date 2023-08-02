<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\Order\Services;

use Exception;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidCartItemsException;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidQuantityException;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidUrlException;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Address;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Cart;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Customer;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\DeliveryMethod;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Item\DiscountItem;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Item\HandlingItem;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Item\InvoiceFeeItem;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Item\OtherPaymentItem;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Item\ProductItem;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Merchant;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\MerchantReference;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Platform;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\PreviousOrder;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Tracking\TrackingPickupPoint;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Tracking\TrackingPickupStore;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Tracking\TrackingPostal;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\UpdateOrderRequest;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Vehicle;
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

        $this->orderService = TestServiceRegister::getService(OrderService::class);
        $this->orderRepository = TestServiceRegister::getService(SeQuraOrderRepositoryInterface::class);

        $httpClient = TestServiceRegister::getService(HttpClient::class);
        $this->httpClient = $httpClient;
        TestServiceRegister::registerService(HttpClient::class, static function () use ($httpClient) {
            return $httpClient;
        });
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

        $updateOrderRequest = $this->getUpdateOrderRequestExample();

        // Act
        $response = $this->orderService->updateOrder($updateOrderRequest);

        // Assert
        self::assertEquals($this->expectedToArrayResponse(), $response->toArray());
    }

    /**
     * Returns UpdateOrderRequest example.
     *
     * @return UpdateOrderRequest
     *
     * @throws InvalidCartItemsException
     * @throws InvalidQuantityException
     * @throws InvalidUrlException
     */
    private function getUpdateOrderRequestExample(): UpdateOrderRequest
    {
        $merchant = new Merchant(
            'logeecom',
            'https://testNotifyUrl',
            [
                'signature' => 'testSignature',
                'testParam1Key' => 'testParam1Value',
            ],
            'testReturnUrl',
            'testApprovedCallback',
            'testEditUrl',
            'testAbortUrl',
            'testRejectCallback',
            'testPartPaymentDetailsGetter',
            'testApprovedUrl',
            'testOperatorRef'
        );

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
                new HandlingItem('testItemReference4','testName4',5),
                new InvoiceFeeItem(30),
                new DiscountItem('testItemReference5','testName5',-20),
                new OtherPaymentItem('testItemReference3','testName3',-5)
            ],
            'testCartRef',
            'testCreatedAt',
            'testUpdatedAt'
        );

        $shippedCart = new Cart('EUR', false);

        $deliveryMethod = new DeliveryMethod('testName','testDays','testProvider', false);
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

        $customer = new Customer(
            'test@test.test',
            'testCode',
            'testIpNum',
            'testAgent',
            'testGivenNames',
            'testSurnames',
            'testTitle',
            'testRef',
            'testDateOfBirth',
            'testNin',
            'testCompany',
            'testVetNumber',
            'testCreatedAt',
            'testUpdatedAt',
            10,
            'testNinControl',
            [
                new PreviousOrder(
                    'testCreatedAt1',
                    10,
                    'testCurrency1',
                    'testRawStatus1',
                    'testStatus1',
                    'testPaymentMethodRaw1',
                    'testPaymentMethod1',
                    'testPostalCode1',
                    'testCountryCode1'
                ),
                new PreviousOrder(
                    'testCreatedAt2',
                    20,
                    'testCurrency2',
                    'testRawStatus2',
                    'testStatus2',
                    'testPaymentMethodRaw2',
                    'testPaymentMethod2',
                    'testPostalCode2',
                    'testCountryCode2'
                )
            ],
            new Vehicle('testPlaque','testBrand','testModel','testFrame','testFirstRegistrationDate'),
            true
        );

        $platform = new Platform(
            'testName',
            'testVersion',
            'testUName',
            'testDbName',
            'testDbVersion',
            'testPluginVersion',
            'testPhpVersion'
        );

        $merchantReference = new MerchantReference('ZXCV1234', 'testOrderRef2');
        $trackings = [
            new TrackingPickupStore(
                'testReference1',
                'testTrackingNumber1',
                '2222-02-02T22:22:22+01:00',
                'testOperatorRef1',
                'testStoreRef1',
                '2222-02-02T22:22:22+01:00',
                'testAddressLine11',
                'testAddressLine21',
                'testPostalCode1',
                'testCity1',
                'testState1',
                'ES'
            ),
            new TrackingPickupPoint(
                'testReference2',
                'testTrackingNumber2',
                '2222-02-02T22:22:22+01:00',
                'testOperatorRef2',
                'testStoreRef2',
                '2222-02-02T22:22:22+01:00',
                'testAddressLine12',
                'testAddressLine22',
                'testPostalCode2',
                'testCity2',
                'testState2',
                'ES'),
            new TrackingPostal(
                'testReference3',
                'testCarrier',
                'testTrackingNumber3',
                '2222-02-02T22:22:22+01:00',
                'https://testTrackingUrl'
            )
        ];

        return new UpdateOrderRequest(
            $merchant,
            $merchantReference,
            $platform,
            $unshippedCart,
            $shippedCart,
            $deliveryMethod,
            $customer,
            $deliveryAddress,
            $invoiceAddress,
            $trackings
        );
    }

    /**
     * @return array
     */
    private function expectedToArrayResponse(): array
    {
        return [
            "merchant" => [
                "id" => "logeecom",
                "notify_url" => "https://wix.sequra.test/api/webhooks",
                "notification_parameters" => [
                    "signature" => "K6hDNSwfcJjF+suAJqXAjA==",
                    "test1" => "test"
                ],
                "return_url" => "https://cashier-services.wix.com/_api/payment-services-web/plugin/redirections/ba1a2231-ff21-43db-af2a-6c63703f5781/approved",
                "approved_callback" => "",
                "edit_url" => "",
                "abort_url" => "https://cashier-services.wix.com/_api/payment-services-web/plugin/redirections/ba1a2231-ff21-43db-af2a-6c63703f5781/canceled",
                "rejected_callback" => "",
                "partpayment_details_getter" => "",
                "approved_url" => "",
                "options" => [
                    "has_jquery" => false
                ],
                "events_webhook" => [
                    "url" => "https://wix.sequra.test/api/webhooks"
                ]
            ],
            "merchant_reference" => [
                "order_ref_1" => "ZXCV1234",
                "order_ref_2" => "0080-1234-4343-5353"
            ],
            "cart" => [
                "currency" => "EUR",
                "order_total_with_tax" => 6097,
                "items" => [
                    [
                        "type" => "product",
                        "total_with_tax" => 170000,
                        "reference" => "9ca7d219-60ae-c8b6-15ae-8eb54be7ba44",
                        "name" => "Polvo de perla",
                        "price_with_tax" => 170000,
                        "quantity" => 1,
                        "downloadable" => false
                    ],
                    [
                        "type" => "handling",
                        "reference" => "seur24",
                        "name" => "SEUR entrega en 24 horas",
                        "total_with_tax" => 242
                    ],
                    [
                        "type" => "invoice_fee",
                        "total_with_tax" => 295
                    ],
                    [
                        "type" => "discount",
                        "reference" => "HALFOFF",
                        "name" => "50 % off the full basket!",
                        "total_with_tax" => -750
                    ],
                    [
                        "type" => "other_payment",
                        "reference" => "CASH",
                        "name" => "Paid in cash",
                        "total_with_tax" => -750
                    ]
                ]
            ],
            "delivery_method" => [
                "name" => "SEUR24",
                "days" => "¡Entrega día siguiente!",
                "provider" => "Correos",
                "home_delivery" => true
            ],
            "delivery_address" => [
                "given_names" => "Maria José",
                "surnames" => "Barroso Rajoy",
                "company" => "",
                "address_line_1" => "C/ Aragó 383",
                "address_line_2" => "5º",
                "postal_code" => "08013",
                "city" => "Barcelona",
                "country_code" => "ES",
                "phone" => "933 033 033",
                "mobile_phone" => "615 615 615",
                "state" => "Barcelona",
                "extra" => "I'm home between 9 and 12.",
                "vat_number" => "B12345"
            ],
            "invoice_address" => [
                "given_names" => "Maria José",
                "surnames" => "Barroso Rajoy",
                "company" => "",
                "address_line_1" => "",
                "address_line_2" => "",
                "postal_code" => "",
                "city" => "",
                "country_code" => "ES",
                "phone" => "933 033 033",
                "mobile_phone" => "615 615 615",
                "state" => "Tarragona",
                "extra" => "I'm home between 9 and 12.",
                "vat_number" => "B12345"
            ],
            "customer" => [
                "given_names" => "Maria José",
                "surnames" => "Barroso Rajoy",
                "title" => "mr",
                "email" => "nisse@example.com",
                "ref" => 123,
                "date_of_birth" => "1980-01-20",
                "nin" => "13003009L",
                "company" => "",
                "vat_number" => "B12345",
                "rating" => 100,
                "nin_control" => "15012018F2"
            ],
            "platform" => [
                "name" => "Navision/www.theshop.es",
                "version" => "1.1",
                "plugin_version" => "1.0.2",
                "uname" => "Darwin roatan.local 13.0.0 Darwin Kernel Version 13.0... x86_64",
                "db_name" => "MSSQL",
                "db_version" => "1.2.3"
            ],
            "gui" => [
                "layout" => "desktop"
            ]
        ];
    }
}