<?php

namespace SeQura\Core\Tests\BusinessLogic\SeQuraAPI\OrderReport;

use Exception;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidEnvironmentException;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\AuthorizationCredentials;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use SeQura\Core\BusinessLogic\Domain\Connection\RepositoryContracts\ConnectionDataRepositoryInterface;
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
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Item\ServiceItem;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Merchant;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\MerchantReference;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Platform;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\PreviousOrder;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Tracking\TrackingPickupPoint;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Tracking\TrackingPickupStore;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Tracking\TrackingPostal;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Vehicle;
use SeQura\Core\BusinessLogic\Domain\OrderReport\Models\OrderReport;
use SeQura\Core\BusinessLogic\Domain\OrderReport\Models\OrderStatistics;
use SeQura\Core\BusinessLogic\Domain\OrderReport\Models\SendOrderReportRequest;
use SeQura\Core\BusinessLogic\Domain\OrderReport\Models\Statistics;
use SeQura\Core\BusinessLogic\Domain\OrderReport\ProxyContracts\OrderReportProxyInterface;
use SeQura\Core\BusinessLogic\SeQuraAPI\BaseProxy;
use SeQura\Core\BusinessLogic\SeQuraAPI\Exceptions\HttpApiUnauthorizedException;
use SeQura\Core\Infrastructure\Http\HttpClient;
use SeQura\Core\Infrastructure\Http\HttpResponse;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TestHttpClient;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class OrderReportProxyTest
 *
 * @package SeQura\Core\Tests\BusinessLogic\SeQuraAPI\OrderReport
 */
class OrderReportProxyTest extends BaseTestCase
{
    /**
     * @var OrderReportProxyInterface
     */
    public $proxy;

    /**
     * @var TestHttpClient
     */
    public $httpClient;

    /**
     * @return void
     *
     * @throws RepositoryClassException
     * @throws InvalidEnvironmentException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $httpClient = TestServiceRegister::getService(HttpClient::class);
        $this->httpClient = $httpClient;
        TestServiceRegister::registerService(HttpClient::class, function () {
            return $this->httpClient;
        });

        $this->proxy = TestServiceRegister::getService(OrderReportProxyInterface::class);
        $repository = TestServiceRegister::getService(ConnectionDataRepositoryInterface::class);

        $connectionData = new ConnectionData(
            BaseProxy::TEST_MODE,
            'test',
            new AuthorizationCredentials('test_username', 'test_password')
        );

        $repository->setConnectionData($connectionData);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testSendReportUrl(): void
    {
        $this->httpClient->setMockResponses([new HttpResponse(204, ['UUID' => 'testUUID'], '')]);
        $this->proxy->sendReport($this->generateMinimalSendReportRequest());

        self::assertCount(1, $this->httpClient->getHistory());
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertStringContainsString('orders/delivery_reports', $lastRequest['url']);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testSendReportAuthHeader(): void
    {
        $this->httpClient->setMockResponses([new HttpResponse(204, ['UUID' => 'testUUID'], '')]);
        $this->proxy->sendReport($this->generateMinimalSendReportRequest());

        self::assertCount(1, $this->httpClient->getHistory());
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertArrayHasKey('Authorization', $lastRequest['headers']);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testSendReportMethod(): void
    {
        $this->httpClient->setMockResponses([new HttpResponse(204, ['UUID' => 'testUUID'], '')]);
        $this->proxy->sendReport($this->generateMinimalSendReportRequest());

        self::assertCount(1, $this->httpClient->getHistory());
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertEquals(HttpClient::HTTP_METHOD_POST, $lastRequest['method']);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testSendReportMinimalRequestBody(): void
    {
        $this->httpClient->setMockResponses([new HttpResponse(204, ['UUID' => 'testUUID'], '')]);
        $expectedRequestBody = file_get_contents(
            __DIR__ . '/../../Common/ApiRequests/OrderReport/SendReportRequests/MinimalSendReportRequestBody.json'
        );

        $sendReportRequest = $this->generateMinimalSendReportRequest();
        $this->proxy->sendReport($sendReportRequest);

        self::assertCount(1, $this->httpClient->getHistory());
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertEquals(json_decode($expectedRequestBody, true), json_decode($lastRequest['body'], true));
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testSendReportFullRequestBody(): void
    {
        $this->httpClient->setMockResponses([new HttpResponse(204, ['UUID' => 'testUUID'], '')]);
        $expectedRequestBody = file_get_contents(
            __DIR__ . '/../../Common/ApiRequests/OrderReport/SendReportRequests/FullSendReportRequestBody.json'
        );

        $sendReportRequest = $this->generateFullSendReportRequest();
        $this->proxy->sendReport($sendReportRequest);

        self::assertCount(1, $this->httpClient->getHistory());
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertEquals(json_decode($expectedRequestBody, true), json_decode($lastRequest['body'], true));
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testMinimalSendReportSuccessfulResponse(): void
    {
        $this->httpClient->setMockResponses([new HttpResponse(204, ['location' => 'https://sandbox.sequrapi.com/orders/delivery_reports'], '')]);

        $sendReportRequest = $this->generateMinimalSendReportRequest();
        $response = $this->proxy->sendReport($sendReportRequest);

        self::assertTrue($response);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testFullSendReportSuccessfulResponse(): void
    {
        $this->httpClient->setMockResponses([new HttpResponse(204, ['location' => 'https://sandbox.sequrapi.com/orders/delivery_reports'], '')]);

        $sendReportRequest = $this->generateFullSendReportRequest();
        $response = $this->proxy->sendReport($sendReportRequest);

        self::assertTrue($response);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testSendReportUnauthorizedResponse(): void
    {
        $exception = null;
        $rawResponseBody = file_get_contents(__DIR__ . '/../../Common/ApiResponses/InvalidCredentialsResponse.txt');
        $this->httpClient->setMockResponses([new HttpResponse(401, [], $rawResponseBody)]);

        try {
            $this->proxy->sendReport($this->generateMinimalSendReportRequest());
        } catch (HttpApiUnauthorizedException $exception) {
        }

        $responseBody = json_decode($rawResponseBody, true);

        self::assertNotNull($exception);
        self::assertEquals('Wrong credentials.', $exception->getMessage());
        self::assertEquals(401, $exception->getCode());
        self::assertEquals($responseBody['errors'] ?? [], $exception->getErrors());
    }

    /**
     * @return SendOrderReportRequest
     *
     * @throws InvalidUrlException
     */
    private function generateMinimalSendReportRequest(): SendOrderReportRequest
    {
        $merchant = new Merchant('testMerchantId');
        $platform = new Platform('testName', 'testVersion', 'testUName', 'testDbName', 'testDbVersion');
        $orders = [];

        return new SendOrderReportRequest($merchant, $orders, $platform);
    }

    /**
     * @return SendOrderReportRequest
     *
     * @throws Exception
     */
    private function generateFullSendReportRequest(): SendOrderReportRequest
    {
        $merchant = new Merchant('testMerchantId');
        $platform = new Platform(
            'testName',
            'testVersion',
            'testUName',
            'testDbName',
            'testDbVersion',
            'testPluginVersion',
            'testPhpVersion'
        );

        $orders = [$this->generateOrderReport('1'), $this->generateOrderReport('2')];
        $statistics = new Statistics([
            $this->generateStatisticsReport('1'),
            $this->generateStatisticsReport('2')
        ]);

        return new SendOrderReportRequest($merchant, $orders, $platform, $statistics);
    }

    /**
     * @param string $id
     *
     * @return OrderReport
     *
     * @throws Exception
     */
    private function generateOrderReport(string $id): OrderReport
    {
        $state = 'shipped';
        $sentAt = '2222-02-02T22:22:22+01:00';
        $merchantReference = new MerchantReference('testOrderRef1' . $id, 'testOrderRef2' . $id);
        $deliveryMethod = new DeliveryMethod('testName' . $id, 'testDays' . $id, 'testProvider' . $id, false);
        $trackings = [
            new TrackingPickupStore(
                'testReference1' . $id,
                'testTrackingNumber1' . $id,
                '2222-02-02T22:22:22+01:00',
                'testOperatorRef1' . $id,
                'testStoreRef1' . $id,
                '2222-02-02T22:22:22+01:00',
                'testAddressLine11' . $id,
                'testAddressLine21' . $id,
                'testPostalCode1' . $id,
                'testCity1' . $id,
                'testState1' . $id,
                'ES'
            ),
            new TrackingPickupPoint(
                'testReference2' . $id,
                'testTrackingNumber2' . $id,
                '2222-02-02T22:22:22+01:00',
                'testOperatorRef2' . $id,
                'testStoreRef2' . $id,
                '2222-02-02T22:22:22+01:00',
                'testAddressLine12' . $id,
                'testAddressLine22' . $id,
                'testPostalCode2' . $id,
                'testCity2' . $id,
                'testState2' . $id,
                'ES'
            ),
            new TrackingPostal(
                'testReference3' . $id,
                'testCarrier3' . $id,
                'testTrackingNumber3' . $id,
                '2222-02-02T22:22:22+01:00',
                'https://testTrackingUrl' . $id
            )
        ];


        $cart = new Cart(
            'EUR',
            false,
            [
                new ProductItem(
                    'testItemReference1' . $id,
                    'testName1' . $id,
                    5,
                    2,
                    10,
                    false,
                    true,
                    true,
                    true,
                    'testCategory' . $id,
                    'testDescription' . $id,
                    'testManufacturer' . $id,
                    'testSupplier' . $id,
                    'testProductId' . $id,
                    'testUrl' . $id,
                    'testTrackingReference' . $id
                ),
                new ServiceItem(
                    'testItemReference2' . $id,
                    'testName2' . $id,
                    5,
                    2,
                    false,
                    10,
                    null,
                    'P3Y6M4DT12H30M5S',
                    'testSupplier' . $id,
                    true
                ),
                new HandlingItem('testItemReference4' . $id, 'testName4' . $id, 5),
                new InvoiceFeeItem(30),
                new DiscountItem('testItemReference5' . $id, 'testName5' . $id, -20),
                new OtherPaymentItem('testItemReference3' . $id, 'testName3' . $id, -5)
            ],
            'testCartRef' . $id,
            'testCreatedAt' . $id,
            'testUpdatedAt' . $id
        );

        $deliveryAddress = new Address(
            'testDeliveryAddressCompany' . $id,
            'testDeliveryAddressLine1' . $id,
            'testDeliveryAddressLine2' . $id,
            'testDeliveryAddressPostalCode' . $id,
            'testDeliveryAddressCity' . $id,
            'ES',
            'testDeliveryAddressGivenNames' . $id,
            'testDeliveryAddressSurnames' . $id,
            'testDeliveryAddressPhone' . $id,
            'testDeliveryAddressMobilePhone' . $id,
            'testDeliveryAddressState' . $id,
            'testDeliveryAddressExtra' . $id,
            'testDeliveryAddressVatNumber' . $id
        );

        $invoiceAddress = new Address(
            'testInvoiceAddressCompany' . $id,
            'testInvoiceAddressLine1' . $id,
            'testInvoiceAddressLine2' . $id,
            'testInvoiceAddressPostalCode' . $id,
            'testInvoiceAddressCity' . $id,
            'ES',
            'testInvoiceAddressGivenNames' . $id,
            'testInvoiceAddressSurnames' . $id,
            'testInvoiceAddressPhone' . $id,
            'testInvoiceAddressMobilePhone' . $id,
            'testInvoiceAddressState' . $id,
            'testInvoiceAddressExtra' . $id,
            'testInvoiceAddressVatNumber' . $id
        );

        $customer = new Customer(
            'test@test.test' . $id,
            'testCode' . $id,
            'testIpNum' . $id,
            'testAgent' . $id,
            'testGivenNames' . $id,
            'testSurnames' . $id,
            'testTitle' . $id,
            'testRef' . $id,
            'testDateOfBirth' . $id,
            'testNin' . $id,
            'testCompany' . $id,
            'testVetNumber' . $id,
            'testCreatedAt' . $id,
            'testUpdatedAt' . $id,
            10,
            'testNinControl' . $id,
            [
                new PreviousOrder(
                    'testCreatedAt1' . $id,
                    10,
                    'testCurrency1' . $id,
                    'testRawStatus1' . $id,
                    'testStatus1' . $id,
                    'testPaymentMethodRaw1' . $id,
                    'testPaymentMethod1' . $id,
                    'testPostalCode1' . $id,
                    'testCountryCode1' . $id
                ),
                new PreviousOrder(
                    'testCreatedAt2' . $id,
                    20,
                    'testCurrency2' . $id,
                    'testRawStatus2' . $id,
                    'testStatus2' . $id,
                    'testPaymentMethodRaw2' . $id,
                    'testPaymentMethod2' . $id,
                    'testPostalCode2' . $id,
                    'testCountryCode2' . $id
                )
            ],
            new Vehicle(
                'testPlaque' . $id,
                'testBrand' . $id,
                'testModel' . $id,
                'testFrame' . $id,
                'testFirstRegistrationDate' . $id
            ),
            true
        );

        $remainingCart = new Cart(
            'EUR',
            false,
            [
                new ProductItem(
                    'testRemainingItemReference1' . $id,
                    'testRemainingName1' . $id,
                    5,
                    2,
                    10,
                    false,
                    true,
                    true,
                    true,
                    'testRemainingCategory' . $id,
                    'testRemainingDescription' . $id,
                    'testRemainingManufacturer' . $id,
                    'testRemainingSupplier' . $id,
                    'testRemainingProductId' . $id,
                    'testRemainingUrl' . $id,
                    'testRemainingTrackingReference' . $id
                ),
                new ServiceItem(
                    'testRemainingItemReference2' . $id,
                    'testRemainingName2' . $id,
                    5,
                    2,
                    false,
                    10,
                    null,
                    'P3Y6M4DT12H30M5S',
                    'testRemainingSupplier' . $id,
                    true
                ),
                new HandlingItem('testRemainingItemReference4' . $id, 'testRemainingName4' . $id, 5),
                new InvoiceFeeItem(30),
                new DiscountItem('testRemainingItemReference5' . $id, 'testRemainingName5' . $id, -20),
                new OtherPaymentItem('testRemainingItemReference3' . $id, 'testRemainingName3' . $id, -5)
            ],
            'testRemainingCartRef' . $id,
            'testRemainingCreatedAt' . $id,
            'testRemainingUpdatedAt' . $id
        );

        return new OrderReport(
            $state,
            $merchantReference,
            $cart,
            $deliveryMethod,
            $customer,
            $sentAt,
            $trackings,
            $remainingCart,
            $deliveryAddress,
            $invoiceAddress
        );
    }

    /**
     * @param string $id
     *
     * @return OrderStatistics
     */
    private function generateStatisticsReport(string $id): OrderStatistics
    {
        return new OrderStatistics(
            '2013-12-24T13:14:15+0100',
            'EUR',
            9957,
            new MerchantReference('ZXCV1234' . $id, '0080-1234-4343-5353' . $id),
            'CC' . $id,
            'ES',
            'tablet/ipad4',
            'shipped',
            'Delivered',
            true
        );
    }
}
