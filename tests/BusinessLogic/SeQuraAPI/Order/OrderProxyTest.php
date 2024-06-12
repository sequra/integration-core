<?php

namespace SeQura\Core\Tests\BusinessLogic\SeQuraAPI\Order;

use DateTime;
use Exception;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidEnvironmentException;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\AuthorizationCredentials;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use SeQura\Core\BusinessLogic\Domain\Connection\RepositoryContracts\ConnectionDataRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Address;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Cart;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\CreateOrderRequest;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Customer;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\DeliveryMethod;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\EventsWebhook;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Gui;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Item\DiscountItem;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Item\HandlingItem;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Item\InvoiceFeeItem;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Item\OtherPaymentItem;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Item\ProductItem;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Item\ServiceItem;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Merchant;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\MerchantReference;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Options;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Platform;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\PreviousOrder;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Tracking\TrackingPickupPoint;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Tracking\TrackingPickupStore;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Tracking\TrackingPostal;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\UpdateOrderRequest;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Vehicle;
use SeQura\Core\BusinessLogic\Domain\Order\Models\GetAvailablePaymentMethodsRequest;
use SeQura\Core\BusinessLogic\Domain\Order\Models\GetFormRequest;
use SeQura\Core\BusinessLogic\Domain\Order\ProxyContracts\OrderProxyInterface;
use SeQura\Core\BusinessLogic\SeQuraAPI\BaseProxy;
use SeQura\Core\BusinessLogic\SeQuraAPI\Exceptions\HttpApiInvalidUrlParameterException;
use SeQura\Core\BusinessLogic\SeQuraAPI\Exceptions\HttpApiNotFoundException;
use SeQura\Core\BusinessLogic\SeQuraAPI\Exceptions\HttpApiUnauthorizedException;
use SeQura\Core\Infrastructure\Http\Exceptions\HttpRequestException;
use SeQura\Core\Infrastructure\Http\HttpClient;
use SeQura\Core\Infrastructure\Http\HttpResponse;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TestHttpClient;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class OrderProxyTest
 *
 * @package SeQura\Core\Tests\BusinessLogic\SeQuraAPI\Order
 */
class OrderProxyTest extends BaseTestCase
{
    /**
     * @var OrderProxyInterface
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

        $this->proxy = TestServiceRegister::getService(OrderProxyInterface::class);

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
     * @throws HttpRequestException
     */
    public function testGetPaymentMethodsUrl(): void
    {
        $this->httpClient->setMockResponses([
            new HttpResponse(200, [], file_get_contents(
                __DIR__ . '/../../Common/ApiResponses/Order/GetPaymentMethodsResponses/SuccessfulResponse.json'
            ))
        ]);

        $this->proxy->getAvailablePaymentMethods(new GetAvailablePaymentMethodsRequest('testId'));

        self::assertCount(1, $this->httpClient->getHistory());
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertStringContainsString('orders/testId/payment_methods', $lastRequest['url']);
    }

    /**
     * @return void
     *
     * @throws HttpRequestException
     */
    public function testGetPaymentMethodsAuthHeader(): void
    {
        $this->httpClient->setMockResponses([
            new HttpResponse(200, [], file_get_contents(
                __DIR__ . '/../../Common/ApiResponses/Order/GetPaymentMethodsResponses/SuccessfulResponse.json'
            ))
        ]);

        $this->proxy->getAvailablePaymentMethods(new GetAvailablePaymentMethodsRequest('testId'));

        self::assertCount(1, $this->httpClient->getHistory());
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertArrayHasKey('Authorization', $lastRequest['headers']);
    }

    /**
     * @return void
     *
     * @throws HttpRequestException
     */
    public function testGetPaymentMethodsMethod(): void
    {
        $this->httpClient->setMockResponses([
            new HttpResponse(200, [], file_get_contents(
                __DIR__ . '/../../Common/ApiResponses/Order/GetPaymentMethodsResponses/SuccessfulResponse.json'
            ))
        ]);

        $this->proxy->getAvailablePaymentMethods(new GetAvailablePaymentMethodsRequest('testId'));

        self::assertCount(1, $this->httpClient->getHistory());
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertEquals(HttpClient::HTTP_METHOD_GET, $lastRequest['method']);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testGetPaymentMethodsSuccessfulResponse(): void
    {
        $rawResponseBody = file_get_contents(
            __DIR__ . '/../../Common/ApiResponses/Order/GetPaymentMethodsResponses/SuccessfulResponse.json'
        );

        $this->httpClient->setMockResponses([new HttpResponse(200, [], $rawResponseBody)]);
        $response = $this->proxy->getAvailablePaymentMethods(new GetAvailablePaymentMethodsRequest('testId'));
        $responseBody = json_decode($rawResponseBody, true);
        $paymentMethods = [];

        foreach ($responseBody['payment_options'] as $option) {
            foreach ($option['methods'] as $method) {
                $paymentMethods[] = $method;
            }
        }

        for ($i = 0, $iMax = count($paymentMethods); $i < $iMax; $i++) {
            self::assertEquals($paymentMethods[$i]['product'], $response[$i]->getProduct());
            self::assertEquals($paymentMethods[$i]['campaign'], $response[$i]->getCampaign());
            self::assertEquals($paymentMethods[$i]['title'], $response[$i]->getTitle());
            self::assertEquals($paymentMethods[$i]['long_title'], $response[$i]->getLongTitle());
            self::assertEquals($paymentMethods[$i]['claim'], $response[$i]->getClaim());
            self::assertEquals($paymentMethods[$i]['description'], $response[$i]->getDescription());
            self::assertEquals($paymentMethods[$i]['icon'], $response[$i]->getIcon());
            self::assertEquals(new DateTime($paymentMethods[$i]['starts_at']), $response[$i]->getStartsAt());
            self::assertEquals(new DateTime($paymentMethods[$i]['ends_at']), $response[$i]->getEndsAt());
            self::assertEquals($paymentMethods[$i]['min_amount'] ?? null, $response[$i]->getMinAmount());
            self::assertEquals($paymentMethods[$i]['max_amount'] ?? null, $response[$i]->getMaxAmount());
            self::assertEquals($paymentMethods[$i]['cost_description'], $response[$i]->getCostDescription());
            self::assertEquals($paymentMethods[$i]['cost']['setup_fee'], $response[$i]->getCost()->getSetupFee());
            self::assertEquals($paymentMethods[$i]['cost']['instalment_fee'], $response[$i]->getCost()->getInstalmentFee());
            self::assertEquals($paymentMethods[$i]['cost']['down_payment_fees'], $response[$i]->getCost()->getDownPaymentFees());
            self::assertEquals($paymentMethods[$i]['cost']['instalment_total'], $response[$i]->getCost()->getInstalmentTotal());
        }
    }

    /**
     * @return void
     *
     * @throws HttpRequestException
     */
    public function testGetPaymentMethodsInvalidOrderIdResponse(): void
    {
        $exception = null;
        $rawResponseBody = file_get_contents(
            __DIR__ . '/../../Common/ApiResponses/Order/GetPaymentMethodsResponses/InvalidOrderIdResponse.json'
        );

        $this->httpClient->setMockResponses([new HttpResponse(403, [], $rawResponseBody)]);

        try {
            $this->proxy->getAvailablePaymentMethods(new GetAvailablePaymentMethodsRequest('test'));
        } catch (HttpApiInvalidUrlParameterException $exception) {
        }

        $responseBody = json_decode($rawResponseBody, true);
        self::assertNotNull($exception);
        self::assertEquals('Access forbidden.', $exception->getMessage());
        self::assertEquals(403, $exception->getCode());
        self::assertEquals($responseBody['errors'] ?? [], $exception->getErrors());
    }

    /**
     * @return void
     *
     * @throws HttpRequestException
     */
    public function testGetPaymentMethodsUnauthorizedResponse(): void
    {
        $exception = null;
        $rawResponseBody = file_get_contents(__DIR__ . '/../../Common/ApiResponses/InvalidCredentialsResponse.txt');
        $this->httpClient->setMockResponses([new HttpResponse(401, [], $rawResponseBody)]);

        try {
            $this->proxy->getAvailablePaymentMethods(new GetAvailablePaymentMethodsRequest('test'));
        } catch (HttpApiUnauthorizedException $exception) {
        }

        $responseBody = json_decode($rawResponseBody, true);
        self::assertNotNull($exception);
        self::assertEquals('Wrong credentials.', $exception->getMessage());
        self::assertEquals(401, $exception->getCode());
        self::assertEquals($responseBody['errors'] ?? [], $exception->getErrors());
    }

    /**
     * @return void
     *
     * @throws HttpRequestException
     */
    public function testGetPaymentMethodsInCategoriesUrl(): void
    {
        $this->httpClient->setMockResponses([
            new HttpResponse(200, [], file_get_contents(
                __DIR__ . '/../../Common/ApiResponses/Order/GetPaymentMethodsResponses/SuccessfulResponse.json'
            ))
        ]);

        $this->proxy->getAvailablePaymentMethodsInCategories(new GetAvailablePaymentMethodsRequest('testId'));

        self::assertCount(1, $this->httpClient->getHistory());
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertStringContainsString('orders/testId/payment_methods', $lastRequest['url']);
    }

    /**
     * @return void
     *
     * @throws HttpRequestException
     */
    public function testGetPaymentMethodsInCategoriesAuthHeader(): void
    {
        $this->httpClient->setMockResponses([
            new HttpResponse(200, [], file_get_contents(
                __DIR__ . '/../../Common/ApiResponses/Order/GetPaymentMethodsResponses/SuccessfulResponse.json'
            ))
        ]);

        $this->proxy->getAvailablePaymentMethodsInCategories(new GetAvailablePaymentMethodsRequest('testId'));

        self::assertCount(1, $this->httpClient->getHistory());
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertArrayHasKey('Authorization', $lastRequest['headers']);
    }

    /**
     * @return void
     *
     * @throws HttpRequestException
     */
    public function testGetPaymentMethodsInCategoriesMethod(): void
    {
        $this->httpClient->setMockResponses([
            new HttpResponse(200, [], file_get_contents(
                __DIR__ . '/../../Common/ApiResponses/Order/GetPaymentMethodsResponses/SuccessfulResponse.json'
            ))
        ]);

        $this->proxy->getAvailablePaymentMethodsInCategories(new GetAvailablePaymentMethodsRequest('testId'));

        self::assertCount(1, $this->httpClient->getHistory());
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertEquals(HttpClient::HTTP_METHOD_GET, $lastRequest['method']);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testGetPaymentMethodsInCategoriesSuccessfulResponse(): void
    {
        $rawResponseBody = file_get_contents(
            __DIR__ . '/../../Common/ApiResponses/Order/GetPaymentMethodsResponses/SuccessfulResponse.json'
        );

        $this->httpClient->setMockResponses([new HttpResponse(200, [], $rawResponseBody)]);
        $response = $this->proxy->getAvailablePaymentMethodsInCategories(new GetAvailablePaymentMethodsRequest('testId'));
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
     * @return void
     *
     * @throws HttpRequestException
     */
    public function testGetPaymentMethodsInCategoriesInvalidOrderIdResponse(): void
    {
        $exception = null;
        $rawResponseBody = file_get_contents(
            __DIR__ . '/../../Common/ApiResponses/Order/GetPaymentMethodsResponses/InvalidOrderIdResponse.json'
        );

        $this->httpClient->setMockResponses([new HttpResponse(403, [], $rawResponseBody)]);

        try {
            $this->proxy->getAvailablePaymentMethodsInCategories(new GetAvailablePaymentMethodsRequest('test'));
        } catch (HttpApiInvalidUrlParameterException $exception) {
        }

        $responseBody = json_decode($rawResponseBody, true);
        self::assertNotNull($exception);
        self::assertEquals('Access forbidden.', $exception->getMessage());
        self::assertEquals(403, $exception->getCode());
        self::assertEquals($responseBody['errors'] ?? [], $exception->getErrors());
    }

    /**
     * @return void
     *
     * @throws HttpRequestException
     */
    public function testGetPaymentMethodsInCategoriesUnauthorizedResponse(): void
    {
        $exception = null;
        $rawResponseBody = file_get_contents(__DIR__ . '/../../Common/ApiResponses/InvalidCredentialsResponse.txt');
        $this->httpClient->setMockResponses([new HttpResponse(401, [], $rawResponseBody)]);

        try {
            $this->proxy->getAvailablePaymentMethodsInCategories(new GetAvailablePaymentMethodsRequest('test'));
        } catch (HttpApiUnauthorizedException $exception) {
        }

        $responseBody = json_decode($rawResponseBody, true);
        self::assertNotNull($exception);
        self::assertEquals('Wrong credentials.', $exception->getMessage());
        self::assertEquals(401, $exception->getCode());
        self::assertEquals($responseBody['errors'] ?? [], $exception->getErrors());
    }

    /**
     * @return void
     *
     * @throws HttpRequestException
     */
    public function testGetFormUrl(): void
    {
        $this->httpClient->setMockResponses([
            new HttpResponse(200, [], file_get_contents(
                __DIR__ . '/../../Common/ApiResponses/Order/GetFormResponses/SuccessfulResponse.html'
            ))
        ]);

        $this->proxy->getForm(new GetFormRequest('testId'));

        self::assertCount(1, $this->httpClient->getHistory());
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertStringContainsString('orders/testId/form_v2', $lastRequest['url']);
    }

    /**
     * @return void
     *
     * @throws HttpRequestException
     */
    public function testGetFormUrlWithQueryParameters(): void
    {
        $this->httpClient->setMockResponses([
            new HttpResponse(200, [], file_get_contents(
                __DIR__ . '/../../Common/ApiResponses/Order/GetFormResponses/SuccessfulResponse.html'
            ))
        ]);

        $this->proxy->getForm(new GetFormRequest('testId', 'prod1', 'camp1', true));

        self::assertCount(1, $this->httpClient->getHistory());
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertStringContainsString(
            'orders/testId/form_v2?product=prod1&campaign=camp1&ajax=1',
            $lastRequest['url']
        );
    }

    /**
     * @return void
     *
     * @throws HttpRequestException
     */
    public function testGetFormHeaders(): void
    {
        $this->httpClient->setMockResponses([
            new HttpResponse(200, [], file_get_contents(
                __DIR__ . '/../../Common/ApiResponses/Order/GetFormResponses/SuccessfulResponse.html'
            ))
        ]);

        $this->proxy->getForm(new GetFormRequest('testId'));

        self::assertCount(1, $this->httpClient->getHistory());
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertArrayHasKey('Authorization', $lastRequest['headers']);
        self::assertArrayHasKey('Accept', $lastRequest['headers']);
        self::assertEquals('Accept: text/html', $lastRequest['headers']['Accept']);
    }

    /**
     * @return void
     *
     * @throws HttpRequestException
     */
    public function testGetFormMethod(): void
    {
        $this->httpClient->setMockResponses([
            new HttpResponse(200, [], file_get_contents(
                __DIR__ . '/../../Common/ApiResponses/Order/GetFormResponses/SuccessfulResponse.html'
            ))
        ]);

        $this->proxy->getForm(new GetFormRequest('testId'));

        self::assertCount(1, $this->httpClient->getHistory());
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertEquals(HttpClient::HTTP_METHOD_GET, $lastRequest['method']);
    }

    /**
     * @return void
     *
     * @throws HttpRequestException
     */
    public function testGetFormSuccessfulResponse(): void
    {
        $rawResponseBody = file_get_contents(
            __DIR__ . '/../../Common/ApiResponses/Order/GetFormResponses/SuccessfulResponse.html'
        );

        $this->httpClient->setMockResponses([new HttpResponse(200, [], $rawResponseBody)]);
        $response = $this->proxy->getForm(new GetFormRequest('testId'));

        self::assertEquals($rawResponseBody, $response->getForm());
    }

    /**
     * @return void
     *
     * @throws HttpRequestException
     */
    public function testGetFormInvalidOrderIdResponse(): void
    {
        $exception = null;
        $rawResponseBody = file_get_contents(
            __DIR__ . '/../../Common/ApiResponses/Order/GetFormResponses/InvalidOrderIdResponse.html'
        );

        $this->httpClient->setMockResponses([new HttpResponse(404, [], $rawResponseBody)]);

        try {
            $this->proxy->getForm(new GetFormRequest('test'));
        } catch (HttpApiNotFoundException $exception) {
        }

        self::assertNotNull($exception);
        self::assertEquals('Page not found.', $exception->getMessage());
        self::assertEquals(404, $exception->getCode());
        self::assertEquals([], $exception->getErrors());
    }

    /**
     * @return void
     *
     * @throws HttpRequestException
     */
    public function testGetFormUnauthorizedResponse(): void
    {
        $exception = null;
        $rawResponseBody = file_get_contents(__DIR__ . '/../../Common/ApiResponses/InvalidCredentialsResponse.txt');
        $this->httpClient->setMockResponses([new HttpResponse(401, [], $rawResponseBody)]);

        try {
            $this->proxy->getForm(new GetFormRequest('test'));
        } catch (HttpApiUnauthorizedException $exception) {
        }

        $responseBody = json_decode($rawResponseBody, true);
        self::assertNotNull($exception);
        self::assertEquals('Wrong credentials.', $exception->getMessage());
        self::assertEquals(401, $exception->getCode());
        self::assertEquals($responseBody['errors'] ?? [], $exception->getErrors());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testCreateOrderUrl(): void
    {
        $this->httpClient->setMockResponses([new HttpResponse(204, ['UUID' => 'testUUID'], '')]);
        $this->proxy->createOrder($this->generateMinimalCreateOrderRequest());

        self::assertCount(1, $this->httpClient->getHistory());
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertStringContainsString('orders', $lastRequest['url']);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testCreateOrderAuthHeader(): void
    {
        $this->httpClient->setMockResponses([new HttpResponse(204, ['UUID' => 'testUUID'], '')]);
        $this->proxy->createOrder($this->generateMinimalCreateOrderRequest());

        self::assertCount(1, $this->httpClient->getHistory());
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertArrayHasKey('Authorization', $lastRequest['headers']);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testCreateOrderMethod(): void
    {
        $this->httpClient->setMockResponses([new HttpResponse(204, ['UUID' => 'testUUID'], '')]);
        $this->proxy->createOrder($this->generateMinimalCreateOrderRequest());

        self::assertCount(1, $this->httpClient->getHistory());
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertEquals(HttpClient::HTTP_METHOD_POST, $lastRequest['method']);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testCreateOrderMinimalRequestBody(): void
    {
        $this->httpClient->setMockResponses([new HttpResponse(204, ['UUID' => 'testUUID'], '')]);
        $expectedRequestBody = file_get_contents(
            __DIR__ . '/../../Common/ApiRequests/Order/CreateOrderRequests/MinimalCreateOrderRequestBody.json'
        );

        $createOrderRequest = $this->generateMinimalCreateOrderRequest();
        $this->proxy->createOrder($createOrderRequest);

        self::assertCount(1, $this->httpClient->getHistory());
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertEquals(json_decode($expectedRequestBody, true), json_decode($lastRequest['body'], true));
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testCreateOrderFullRequestBody(): void
    {
        $this->httpClient->setMockResponses([new HttpResponse(204, ['UUID' => 'testUUID'], '')]);
        $expectedRequestBody = file_get_contents(
            __DIR__ . '/../../Common/ApiRequests/Order/CreateOrderRequests/FullCreateOrderRequestBody.json'
        );

        $createOrderRequest = $this->generateFullCreateOrderRequest();
        $this->proxy->createOrder($createOrderRequest);

        self::assertCount(1, $this->httpClient->getHistory());
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertEquals(json_decode($expectedRequestBody, true), json_decode($lastRequest['body'], true));
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testMinimalCreateOrderSuccessfulResponse(): void
    {
        $this->httpClient->setMockResponses([new HttpResponse(204, ['location' => 'https://sandbox.sequrapi.com/orders/testUUID'], '')]);

        $createOrderRequest = $this->generateMinimalCreateOrderRequest();
        $response = $this->proxy->createOrder($createOrderRequest);

        self::assertEquals('testUUID', $response->getReference());
        self::assertEquals($createOrderRequest->getState(), $response->getState());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testFullCreateOrderSuccessfulResponse(): void
    {
        $this->httpClient->setMockResponses([new HttpResponse(204, ['location' => 'https://sandbox.sequrapi.com/orders/testUUID'], '')]);

        $createOrderRequest = $this->generateFullCreateOrderRequest();
        $response = $this->proxy->createOrder($createOrderRequest);

        self::assertEquals('testUUID', $response->getReference());
        self::assertEquals($createOrderRequest->getState(), $response->getState());
        self::assertEquals($createOrderRequest->getCart()->getCartRef(), $response->getCartId());
        self::assertEquals($createOrderRequest->getMerchantReference()->getOrderRef1(), $response->getOrderRef1());
        self::assertEquals(
            $createOrderRequest->getMerchant()->getEventsWebhook()->getParameters()['signature'],
            $response->getMerchant()->getEventsWebhook()->getParameters()['signature']
        );
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testCreateOrderInvalidMerchantIdResponse(): void
    {
        $exception = null;
        $rawResponseBody = file_get_contents(
            __DIR__ . '/../../Common/ApiResponses/Order/CreateOrderResponses/InvalidMerchantIdResponse.json'
        );

        $this->httpClient->setMockResponses([new HttpResponse(403, [], $rawResponseBody)]);

        try {
            $this->proxy->createOrder($this->generateMinimalCreateOrderRequest());
        } catch (HttpApiInvalidUrlParameterException $exception) {
        }

        $responseBody = json_decode($rawResponseBody, true);

        self::assertNotNull($exception);
        self::assertEquals('Access forbidden.', $exception->getMessage());
        self::assertEquals(403, $exception->getCode());
        self::assertEquals($responseBody['errors'] ?? [], $exception->getErrors());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testCreateOrderUnauthorizedResponse(): void
    {
        $exception = null;
        $rawResponseBody = file_get_contents(__DIR__ . '/../../Common/ApiResponses/InvalidCredentialsResponse.txt');
        $this->httpClient->setMockResponses([new HttpResponse(401, [], $rawResponseBody)]);

        try {
            $this->proxy->createOrder($this->generateMinimalCreateOrderRequest());
        } catch (HttpApiUnauthorizedException $exception) {
        }

        $responseBody = json_decode($rawResponseBody, true);

        self::assertNotNull($exception);
        self::assertEquals('Wrong credentials.', $exception->getMessage());
        self::assertEquals(401, $exception->getCode());
        self::assertEquals($responseBody['errors'] ?? [], $exception->getErrors());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testUpdateOrderUrl(): void
    {
        $this->httpClient->setMockResponses([new HttpResponse(204, ['UUID' => 'testUUID'], '')]);
        $this->proxy->updateOrder($this->generateMinimalUpdateOrderRequest());

        self::assertCount(1, $this->httpClient->getHistory());
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertStringContainsString('https://sandbox.sequrapi.com/merchants/testMerchantId/orders/testOrderRef1', $lastRequest['url']);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testUpdateOrderAuthHeader(): void
    {
        $this->httpClient->setMockResponses([new HttpResponse(204, ['UUID' => 'testUUID'], '')]);
        $this->proxy->updateOrder($this->generateMinimalUpdateOrderRequest());

        self::assertCount(1, $this->httpClient->getHistory());
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertArrayHasKey('Authorization', $lastRequest['headers']);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testUpdateOrderMethod(): void
    {
        $this->httpClient->setMockResponses([new HttpResponse(204, ['UUID' => 'testUUID'], '')]);
        $this->proxy->updateOrder($this->generateMinimalUpdateOrderRequest());

        self::assertCount(1, $this->httpClient->getHistory());
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertEquals(HttpClient::HTTP_METHOD_PUT, $lastRequest['method']);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testUpdateOrderMinimalRequestBody(): void
    {
        $this->httpClient->setMockResponses([new HttpResponse(204, ['UUID' => 'testUUID'], '')]);
        $expectedRequestBody = file_get_contents(
            __DIR__ . '/../../Common/ApiRequests/Order/UpdateOrderRequests/MinimalUpdateOrderRequestBody.json'
        );

        $updateOrderRequest = $this->generateMinimalUpdateOrderRequest();
        $this->proxy->updateOrder($updateOrderRequest);

        self::assertCount(1, $this->httpClient->getHistory());
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertEquals(json_decode($expectedRequestBody, true), json_decode($lastRequest['body'], true));
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testUpdateOrderFullRequestBody(): void
    {
        $this->httpClient->setMockResponses([new HttpResponse(204, ['UUID' => 'testUUID'], '')]);
        $expectedRequestBody = file_get_contents(
            __DIR__ . '/../../Common/ApiRequests/Order/UpdateOrderRequests/FullUpdateOrderRequestBody.json'
        );

        $updateOrderRequest = $this->generateFullUpdateOrderRequest();
        $this->proxy->updateOrder($updateOrderRequest);

        self::assertCount(1, $this->httpClient->getHistory());
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertEquals(json_decode($expectedRequestBody, true), json_decode($lastRequest['body'], true));
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testMinimalUpdateOrderSuccessfulResponse(): void
    {
        $this->httpClient->setMockResponses([new HttpResponse(204, ['location' => 'https://sandbox.sequrapi.com/merchants/test/orders/testOrderRef1'], '')]);

        $updateOrderRequest = $this->generateMinimalUpdateOrderRequest();
        $response = $this->proxy->updateOrder($updateOrderRequest);

        self::assertTrue($response);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testFullUpdateOrderSuccessfulResponse(): void
    {
        $this->httpClient->setMockResponses([new HttpResponse(204, ['location' => 'https://sandbox.sequrapi.com/merchants/test/orders/testOrderRef1'], '')]);

        $updateOrderRequest = $this->generateFullUpdateOrderRequest();
        $response = $this->proxy->updateOrder($updateOrderRequest);

        self::assertTrue($response);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testUpdateOrderInvalidMerchantIdResponse(): void
    {
        $exception = null;
        $rawResponseBody = file_get_contents(
            __DIR__ . '/../../Common/ApiResponses/Order/UpdateOrderResponses/InvalidMerchantIdResponse.json'
        );

        $this->httpClient->setMockResponses([new HttpResponse(403, [], $rawResponseBody)]);

        try {
            $this->proxy->updateOrder($this->generateMinimalUpdateOrderRequest());
        } catch (HttpApiInvalidUrlParameterException $exception) {
        }

        $responseBody = json_decode($rawResponseBody, true);

        self::assertNotNull($exception);
        self::assertEquals('Access forbidden.', $exception->getMessage());
        self::assertEquals(403, $exception->getCode());
        self::assertEquals($responseBody['errors'] ?? [], $exception->getErrors());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testUpdateOrderUnauthorizedResponse(): void
    {
        $exception = null;
        $rawResponseBody = file_get_contents(__DIR__ . '/../../Common/ApiResponses/InvalidCredentialsResponse.txt');
        $this->httpClient->setMockResponses([new HttpResponse(401, [], $rawResponseBody)]);

        try {
            $this->proxy->updateOrder($this->generateFullUpdateOrderRequest());
        } catch (HttpApiUnauthorizedException $exception) {
        }

        $responseBody = json_decode($rawResponseBody, true);

        self::assertNotNull($exception);
        self::assertEquals('Wrong credentials.', $exception->getMessage());
        self::assertEquals(401, $exception->getCode());
        self::assertEquals($responseBody['errors'] ?? [], $exception->getErrors());
    }

    /**
     * @return CreateOrderRequest
     *
     * @throws Exception
     */
    private function generateMinimalCreateOrderRequest(): CreateOrderRequest
    {
        $merchant = new Merchant('testMerchantId');
        $merchantReference = new MerchantReference('test123');
        $cart = new Cart('testCurrency', false, [
            new ProductItem('testItemReference', 'testName', 5, 2, 10, false)
        ]);

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

    /**
     * @return CreateOrderRequest
     *
     * @throws Exception
     */
    private function generateFullCreateOrderRequest(): CreateOrderRequest
    {
        $merchant = new Merchant(
            'testMerchantId',
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
            new Options(true, false, true, true),
            new EventsWebhook('https://testUrl', ['signature' => 'testSignature', 'testParam1Key' => 'testParam1Value'])
        );

        $cart = new Cart(
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
                new ServiceItem(
                    'testItemReference2',
                    'testName2',
                    5,
                    2,
                    false,
                    10,
                    null,
                    'P3Y6M4DT12H30M5S',
                    'testSupplier',
                    true
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

        $deliveryMethod = new DeliveryMethod('testName', 'testDays', 'testProvider', false);
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
            new Vehicle('testPlaque', 'testBrand', 'testModel', 'testFrame', 'testFirstRegistrationDate'),
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

        $gui = new Gui(Gui::ALLOWED_VALUES['desktop']);
        $merchantReference = new MerchantReference('testOrderRef1', 'testOrderRef2');
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
                'ES'
            ),
            new TrackingPostal(
                'testReference3',
                'testCarrier',
                'testTrackingNumber3',
                '2222-02-02T22:22:22+01:00',
                'https://testTrackingUrl'
            )
        ];

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
            $merchantReference,
            $trackings
        );
    }

    /**
     * @return UpdateOrderRequest
     *
     * @throws Exception
     */
    private function generateMinimalUpdateOrderRequest(): UpdateOrderRequest
    {
        $merchant = new Merchant('testMerchantId');
        $platform = new Platform('testName', 'testVersion', 'testUName', 'testDbName', 'testDbVersion');
        $merchantReference = new MerchantReference('testOrderRef1');
        $cart = new Cart('testCurrency', false, [
            new ProductItem('testItemReference', 'testName', 5, 2, 10, false)
        ]);


        return new UpdateOrderRequest($merchant, $merchantReference, $platform, $cart, $cart);
    }

    /**
     * @return UpdateOrderRequest
     *
     * @throws Exception
     */
    private function generateFullUpdateOrderRequest(): UpdateOrderRequest
    {
        $merchant = new Merchant(
            'testMerchantId',
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
            new Options(true, false, true, true),
            new EventsWebhook('https://testUrl', ['signature' => 'testSignature', 'testParam1Key' => 'testParam1Value'])
        );

        $cart = new Cart(
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

        $deliveryMethod = new DeliveryMethod('testName', 'testDays', 'testProvider', false);
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
            new Vehicle('testPlaque', 'testBrand', 'testModel', 'testFrame', 'testFirstRegistrationDate'),
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

        $merchantReference = new MerchantReference('testOrderRef1', 'testOrderRef2');
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
                'ES'
            ),
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
            $cart,
            $cart,
            $deliveryMethod,
            $customer,
            $deliveryAddress,
            $invoiceAddress,
            $trackings
        );
    }
}
