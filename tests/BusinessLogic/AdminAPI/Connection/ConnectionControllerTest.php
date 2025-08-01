<?php

namespace SeQura\Core\Tests\BusinessLogic\AdminAPI\Connection;

use Exception;
use SeQura\Core\BusinessLogic\AdminAPI\AdminAPI;
use SeQura\Core\BusinessLogic\AdminAPI\Connection\Requests\ConnectionRequest;
use SeQura\Core\BusinessLogic\AdminAPI\Connection\Requests\OnboardingRequest;
use SeQura\Core\BusinessLogic\AdminAPI\Connection\Responses\OnboardingDataResponse;
use SeQura\Core\BusinessLogic\AdminAPI\Connection\Responses\SuccessfulConnectionResponse;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidEnvironmentException;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\AuthorizationCredentials;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use SeQura\Core\BusinessLogic\Domain\Connection\RepositoryContracts\ConnectionDataRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use SeQura\Core\BusinessLogic\Domain\Connection\Services\CredentialsService;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\StatisticalData\Models\StatisticalData;
use SeQura\Core\BusinessLogic\Domain\StatisticalData\RepositoryContracts\StatisticalDataRepositoryInterface;
use SeQura\Core\BusinessLogic\SeQuraAPI\BaseProxy;
use SeQura\Core\Infrastructure\Http\HttpClient;
use SeQura\Core\Infrastructure\Http\HttpResponse;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockConnectionService;
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
     * @var TestHttpClient
     */
    public $httpClient;
    /**
     * @var ConnectionDataRepositoryInterface
     */
    private $connectionDataRepository;
    /**
     * @var StatisticalDataRepositoryInterface
     */
    private $statisticalDataRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connectionDataRepository = TestServiceRegister::getService(ConnectionDataRepositoryInterface::class);
        $this->statisticalDataRepository = TestServiceRegister::getService(StatisticalDataRepositoryInterface::class);
        $httpClient = TestServiceRegister::getService(HttpClient::class);
        $this->httpClient = $httpClient;
        TestServiceRegister::registerService(HttpClient::class, function () {
            return $this->httpClient;
        });
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testIsConnectionDataValidResponseSuccess(): void
    {
        // Arrange
        $request = new ConnectionRequest(
            BaseProxy::TEST_MODE,
            'logeecom',
            'test_username',
            'test_password',
            'sequra'
        );

        $this->httpClient->setMockResponses([
            new HttpResponse(200, [], file_get_contents(
                __DIR__ . '/../../Common/ApiResponses/Connection/SuccessfulResponse.json'
            ))
        ]);

        // Act
        $response = AdminAPI::get()->connection('1')->isConnectionDataValid($request);

        // Assert
        self::assertTrue($response->isSuccessful());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testIsConnectionDataValidSuccessfulResponseToArray(): void
    {
        // Arrange
        $request = new ConnectionRequest(
            BaseProxy::TEST_MODE,
            'logeecom',
            'test_username',
            'test_password',
            'sequra'
        );

        $this->httpClient->setMockResponses([
            new HttpResponse(200, [], file_get_contents(
                __DIR__ . '/../../Common/ApiResponses/Connection/SuccessfulResponse.json'
            ))
        ]);

        // Act
        $response = AdminAPI::get()->connection('1')->isConnectionDataValid($request);

        // Assert
        self::assertEquals(['isValid' => true, 'reason' => null], $response->toArray());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testIsConnectionDataValidWrongMerchantIdResponseToArray(): void
    {
        // Arrange
        $request = new ConnectionRequest(
            BaseProxy::TEST_MODE,
            'logeecom',
            'test_username',
            'test_password',
            'sequra'
        );

        $this->httpClient->setMockResponses([
            new HttpResponse(403, [], file_get_contents(
                __DIR__ . '/../../Common/ApiResponses/Merchant/GetPaymentMethodsResponses/InvalidMerchantIdResponse.json'
            ))
        ]);

        // Act
        $response = AdminAPI::get()->connection('1')->isConnectionDataValid($request);

        // Assert
        self::assertEquals(['isValid' => false, 'reason' => 'merchantId'], $response->toArray());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testIsConnectionDataValidWrongCredentialsResponseToArray(): void
    {
        // Arrange
        $request = new ConnectionRequest(
            BaseProxy::TEST_MODE,
            'logeecom',
            'test_username',
            'test_password',
            'sequra'
        );

        $this->httpClient->setMockResponses([
            new HttpResponse(401, [], file_get_contents(
                __DIR__ . '/../../Common/ApiResponses/Merchant/GetPaymentMethodsResponses/WrongCredentialsResponse.txt'
            ))
        ]);

        // Act
        $response = AdminAPI::get()->connection('1')->isConnectionDataValid($request);

        // Assert
        self::assertEquals(['isValid' => false, 'reason' => 'username/password'], $response->toArray());
    }

    /**
     * @return void
     */
    public function testConnectionValidationSuccess(): void
    {
        // Arrange
        $request = new ConnectionRequest(
            BaseProxy::TEST_MODE,
            'logeecom',
            'test_username',
            'test_password',
            'sequra'
        );

        $this->httpClient->setMockResponses([
            new HttpResponse(200, [], file_get_contents(
                __DIR__ . '/../../Common/ApiResponses/Connection/SuccessfulResponse.json'
            ))
        ]);

        // Act
        $response = AdminAPI::get()->connection('1')->validateConnectionData($request);

        // Assert
        self::assertTrue($response->isSuccessful());
    }

    /**
     * @return void
     */
    public function testConnectionValidationNotSuccessful(): void
    {
        // Arrange
        $request = new ConnectionRequest(
            BaseProxy::TEST_MODE,
            'test_merchant',
            'test_username',
            'test_password',
            'sequra'
        );

        $this->httpClient->setMockResponses([
            new HttpResponse(403, [], file_get_contents(
                __DIR__ . '/../../Common/ApiResponses/Merchant/GetPaymentMethodsResponses/InvalidMerchantIdResponse.json'
            ))
        ]);

        // Act
        $response = AdminAPI::get()->connection('1')->validateConnectionData($request);

        // Assert
        self::assertFalse($response->isSuccessful());
    }

    /**
     * @return void
     */
    public function testIsSavingConnectionDataResponseSuccessful(): void
    {
        // Arrange
        $request = new ConnectionRequest(
            BaseProxy::TEST_MODE,
            'logeecom',
            'test_username',
            'test_password',
            'sequra'
        );

        // Act
        $response = AdminAPI::get()->connection('1')->saveConnectionData($request);

        // Assert
        self::assertTrue($response->isSuccessful());
    }

    /**
     * @return void
     */
    public function testIsSavingConnectionDataResponseNotSuccessful(): void
    {
        // Arrange
        $request = new ConnectionRequest(
            'test',
            'logeecom',
            'test_username',
            'test_password',
            'sequra'
        );

        // Act
        $response = AdminAPI::get()->connection('1')->saveConnectionData($request);

        //Assert
        self::assertFalse($response->isSuccessful());
    }

    /**
     * @return void
     */
    public function testSavingConnectionDataResponse(): void
    {
        // Arrange
        $request = new ConnectionRequest(
            BaseProxy::TEST_MODE,
            'logeecom',
            'test_username',
            'test_password',
            'sequra'
        );

        // Act
        $response = AdminAPI::get()->connection('1')->saveConnectionData($request);
        $expectedResponse = new SuccessfulConnectionResponse();

        // Assert
        self::assertEquals($expectedResponse, $response);
    }

    /**
     * @return void
     */
    public function testSavingConnectionDataResponseToArray(): void
    {
        // Arrange
        $request = new ConnectionRequest(
            BaseProxy::TEST_MODE,
            'logeecom',
            'test_username',
            'test_password',
            'sequra'
        );

        // Act
        $response = AdminAPI::get()->connection('1')->saveConnectionData($request);

        // Assert
        self::assertEquals(['isValid' => true], $response->toArray());
    }

    /**
     * @throws Exception
     */
    public function testIsUpdateConnectionDataResponseSuccessful(): void
    {
        // Arrange
        $connectionData = new ConnectionData(
            BaseProxy::TEST_MODE,
            'logeecom',
            'sequra',
            new AuthorizationCredentials('test_username', 'test_password')
        );

        StoreContext::doWithStore('1', [$this->connectionDataRepository, 'setConnectionData'], [$connectionData]);

        $request = new ConnectionRequest(
            BaseProxy::TEST_MODE,
            'logeecom2',
            'test_username2',
            'test_password2',
            'sequra'
        );

        // Act
        $response = AdminAPI::get()->connection('1')->saveConnectionData($request);

        // Assert
        self::assertTrue($response->isSuccessful());
    }

    /**
     * @throws Exception
     */
    public function testUpdateConnectionDataResponse(): void
    {
        // Arrange
        $connectionData = new ConnectionData(
            BaseProxy::TEST_MODE,
            'logeecom',
            'sequra',
            new AuthorizationCredentials('test_username', 'test_password')
        );

        StoreContext::doWithStore('1', [$this->connectionDataRepository, 'setConnectionData'], [$connectionData]);

        $request = new ConnectionRequest(
            BaseProxy::TEST_MODE,
            'logeecom2',
            'test_username2',
            'test_password2',
            'sequra'
        );

        // Act
        $response = AdminAPI::get()->connection('1')->saveConnectionData($request);
        $expectedResponse = new SuccessfulConnectionResponse();

        // Assert
        self::assertEquals($expectedResponse, $response);
    }

    /**
     * @throws Exception
     */
    public function testUpdateConnectionDataResponseToArray(): void
    {
        // Arrange
        $connectionData = new ConnectionData(
            BaseProxy::TEST_MODE,
            'logeecom',
            'sequra',
            new AuthorizationCredentials('test_username', 'test_password')
        );

        StoreContext::doWithStore('1', [$this->connectionDataRepository, 'setConnectionData'], [$connectionData]);

        $request = new ConnectionRequest(
            BaseProxy::TEST_MODE,
            'logeecom2',
            'test_username2',
            'test_password2',
            'sequra'
        );

        // Act
        $response = AdminAPI::get()->connection('1')->saveConnectionData($request);

        // Assert
        self::assertEquals(['isValid' => true], $response->toArray());
    }

    /**
     * @throws InvalidEnvironmentException
     */
    public function testIsGetOnboardingDataResponseSuccessful(): void
    {
        // Arrange
        $this->statisticalDataRepository->setStatisticalData(new StatisticalData(true));
        $this->connectionDataRepository->setConnectionData(
            new ConnectionData(
                BaseProxy::TEST_MODE,
                'logeecom',
                'sequra',
                new AuthorizationCredentials('test_username', 'test_password')
            )
        );

        // Act
        $response = AdminAPI::get()->connection('1')->getOnboardingData();

        // Assert
        self::assertTrue($response->isSuccessful());
    }

    /**
     * @throws Exception
     */
    public function testGetOnboardingDataResponse(): void
    {
        // Arrange
        $statisticalData = new StatisticalData(true);
        $connectionDataSeQura = new ConnectionData(
            BaseProxy::TEST_MODE,
            'logeecom',
            'sequra',
            new AuthorizationCredentials('test_username', 'test_password')
        );

        $connectionDataSvea = new ConnectionData(
            BaseProxy::TEST_MODE,
            'logeecom',
            'svea',
            new AuthorizationCredentials('test_username', 'test_password')
        );

        StoreContext::doWithStore('1', [$this->statisticalDataRepository, 'setStatisticalData'], [$statisticalData]);
        StoreContext::doWithStore('1', [$this->connectionDataRepository, 'setConnectionData'], [$connectionDataSeQura]);
        StoreContext::doWithStore('1', [$this->connectionDataRepository, 'setConnectionData'], [$connectionDataSvea]);
        $expectedResponse = new OnboardingDataResponse([$connectionDataSeQura, $connectionDataSvea], $statisticalData);

        // Act
        $response = AdminAPI::get()->connection('1')->getOnboardingData();

        // Assert
        self::assertEquals($expectedResponse, $response);
    }

    /**
     * @throws Exception
     */
    public function testGetOnboardingDataResponseToArray(): void
    {
        // Arrange
        $statisticalData = new StatisticalData(true);
        $connectionDataSeQura = new ConnectionData(
            BaseProxy::TEST_MODE,
            'logeecom',
            'sequra',
            new AuthorizationCredentials('test_username', 'test_password')
        );

        $connectionDataSvea = new ConnectionData(
            BaseProxy::TEST_MODE,
            'logeecom2',
            'svea',
            new AuthorizationCredentials('test_username2', 'test_password2')
        );

        StoreContext::doWithStore('1', [$this->statisticalDataRepository, 'setStatisticalData'], [$statisticalData]);
        StoreContext::doWithStore('1', [$this->connectionDataRepository, 'setConnectionData'], [$connectionDataSeQura]);
        StoreContext::doWithStore('1', [$this->connectionDataRepository, 'setConnectionData'], [$connectionDataSvea]);
        // Act
        $response = AdminAPI::get()->connection('1')->getOnboardingData();

        // Assert
        self::assertEquals($this->expectedOnboardingDataToArrayResponse(), $response->toArray());
    }

    /**
     * @throws Exception
     */
    public function testGetNonExistingOnboardingDataResponseToArray(): void
    {
        // Act
        $response = AdminAPI::get()->connection('1')->getOnboardingData();

        // Assert
        self::assertEquals([], $response->toArray());
    }

    /**
     * @throws Exception
     */
    public function testConnectSuccessful(): void
    {
        // Arrange
        $mockConnectionService = new MockConnectionService(
            TestServiceRegister::getService(ConnectionDataRepositoryInterface::class),
            TestServiceRegister::getService(CredentialsService::class)
        );

        TestServiceRegister::registerService(ConnectionService::class, function () use ($mockConnectionService) {
            return $mockConnectionService;
        });
        $connection1 = new ConnectionRequest(
            BaseProxy::TEST_MODE,
            'test_username',
            'test_password',
            'test',
            'sequra'
        );
        $connection2 = new ConnectionRequest(
            BaseProxy::TEST_MODE,
            'test_username2',
            'test_password2',
            'test',
            'svea'
        );
        $request = new OnboardingRequest([$connection1, $connection2], false);

        // Act
        $response = AdminAPI::get()->connection('1')->connect($request);

        // Assert
        self::assertEquals(['isValid' => true], $response->toArray());
        self::assertTrue($response->isSuccessful());
    }

    /**
     * @throws Exception
     */
    public function testConnectError(): void
    {
        // Arrange
        $mockConnectionService = new MockConnectionService(
            TestServiceRegister::getService(ConnectionDataRepositoryInterface::class),
            TestServiceRegister::getService(CredentialsService::class)
        );

        $mockConnectionService->setThrowError(true);

        TestServiceRegister::registerService(ConnectionService::class, function () use ($mockConnectionService) {
            return $mockConnectionService;
        });

        $request = new ConnectionRequest(
            BaseProxy::TEST_MODE,
            'logeecom',
            'test_username',
            'test_password',
            'sequra'
        );

        // Act
        $response = AdminAPI::get()->connection('1')->connect($request);

        // Assert
        self::assertNotEmpty($response->toArray());
        self::assertFalse($response->isSuccessful());
    }

    /**
     * @return string[]
     */
    private function expectedConnectionDataToArrayResponse(): array
    {
        return [
            'environment' => 'sandbox',
            'username' => 'test_username',
            'password' => 'test_password',
            'merchantId' => 'logeecom'
        ];
    }

    /**
     * @return array
     */
    private function expectedOnboardingDataToArrayResponse(): array
    {
        return [
            'sendStatisticalData' => true,
            'environment' => 'sandbox',
            'connectionData' =>
                [
                    [
                        'username' => 'test_username',
                        'password' => 'test_password',
                        'merchantId' => 'logeecom',
                        'deployment' => 'sequra',
                    ],
                    [
                        'username' => 'test_username2',
                        'password' => 'test_password2',
                        'merchantId' => 'logeecom2',
                        'deployment' => 'svea',
                    ]
                ]
        ];
    }
}
