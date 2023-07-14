<?php

namespace SeQura\Core\Tests\BusinessLogic\AdminAPI\Connection;

use SeQura\Core\BusinessLogic\AdminAPI\Connection\ConnectionController;
use SeQura\Core\BusinessLogic\AdminAPI\Connection\Requests\ConnectionRequest;
use SeQura\Core\BusinessLogic\AdminAPI\Connection\Responses\SuccessfulConnectionResponse;
use SeQura\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use SeQura\Core\BusinessLogic\Domain\StatisticalData\Services\StatisticalDataService;
use SeQura\Core\BusinessLogic\SeQuraAPI\BaseProxy;
use SeQura\Core\Infrastructure\Http\HttpClient;
use SeQura\Core\Infrastructure\Http\HttpResponse;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TestHttpClient;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class ConnectionControllerTest
 *
 * @package SeQura\Core\Tests\BusinessLogic\AdminAPI\Connection
 */
class ConnectionControllerTest extends BaseTestCase
{
    /**
     * @var ConnectionController
     */
    private $controller;
    /**
     * @var TestHttpClient
     */
    public $httpClient;

    protected function setUp(): void
    {
        parent::setUp();

        $httpClient = TestServiceRegister::getService(HttpClient::class);
        $this->httpClient = $httpClient;
        TestServiceRegister::registerService(HttpClient::class, function () {
            return $this->httpClient;
        });

        $this->controller = new ConnectionController(
            TestServiceRegister::getService(ConnectionService::class),
            TestServiceRegister::getService(StatisticalDataService::class)
        );
    }

    /**
     * @return void
     */
    public function testConnectionValidationSuccess(): void
    {
        $request = new ConnectionRequest(
            BaseProxy::TEST_MODE,
            'logeecom',
            'test_username',
            'test_password'
        );

        $this->httpClient->setMockResponses([
            new HttpResponse(200, [], file_get_contents(
                __DIR__ . '/../../Common/ApiResponses/Merchant/GetPaymentMethodsResponses/SuccessfulResponse.json'
            ))
        ]);

        $response = $this->controller->validateConnectionData($request);

        $this->assertInstanceOf(SuccessfulConnectionResponse::class, $response);
    }

    /**
     * @return void
     */
    public function testConnectionValidationError(): void
    {
        $request = new ConnectionRequest(
            BaseProxy::TEST_MODE,
            'test_merchant',
            'test_username',
            'test_password'
        );

        $this->httpClient->setMockResponses([
            new HttpResponse(403, [], file_get_contents(
                __DIR__ . '/../../Common/ApiResponses/Merchant/GetPaymentMethodsResponses/InvalidMerchantIdResponse.json'
            ))
        ]);

        $response = $this->controller->validateConnectionData($request);

        $this->assertNotInstanceOf(SuccessfulConnectionResponse::class, $response);
    }

    /**
     * @return void
     */
    public function testSavingConnectionData(): void
    {
        $request = new ConnectionRequest(
            BaseProxy::TEST_MODE,
            'logeecom',
            'test_username',
            'test_password'
        );

        $response = $this->controller->saveConnectionData($request);

        $this->assertInstanceOf(SuccessfulConnectionResponse::class, $response);
    }
}
