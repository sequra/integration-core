<?php

namespace SeQura\Core\Tests\BusinessLogic\AdminAPI\Connection;

use Exception;
use SeQura\Core\BusinessLogic\AdminAPI\AdminAPI;
use SeQura\Core\BusinessLogic\AdminAPI\Connection\Requests\ConnectionRequest;
use SeQura\Core\BusinessLogic\AdminAPI\Connection\Requests\OnboardingRequest;
use SeQura\Core\BusinessLogic\AdminAPI\Connection\Responses\ConnectionSettingsResponse;
use SeQura\Core\BusinessLogic\AdminAPI\Connection\Responses\OnboardingDataResponse;
use SeQura\Core\BusinessLogic\AdminAPI\Connection\Responses\SuccessfulConnectionResponse;
use SeQura\Core\BusinessLogic\AdminAPI\Connection\Responses\SuccessfulOnboardingResponse;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidEnvironmentException;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\AuthorizationCredentials;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use SeQura\Core\BusinessLogic\Domain\Connection\RepositoryContracts\ConnectionDataRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\StatisticalData\Models\StatisticalData;
use SeQura\Core\BusinessLogic\Domain\StatisticalData\RepositoryContracts\StatisticalDataRepositoryInterface;
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
            'test_password'
        );

        $this->httpClient->setMockResponses([
            new HttpResponse(200, [], file_get_contents(
                __DIR__ . '/../../Common/ApiResponses/Merchant/GetPaymentMethodsResponses/SuccessfulResponse.json'
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
            'test_password'
        );

        $this->httpClient->setMockResponses([
            new HttpResponse(200, [], file_get_contents(
                __DIR__ . '/../../Common/ApiResponses/Merchant/GetPaymentMethodsResponses/SuccessfulResponse.json'
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
            'test_password'
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
            'test_password'
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
            'test_password'
        );

        $this->httpClient->setMockResponses([
            new HttpResponse(200, [], file_get_contents(
                __DIR__ . '/../../Common/ApiResponses/Merchant/GetPaymentMethodsResponses/SuccessfulResponse.json'
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
            'test_password'
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
            'test_password'
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
            'test_password'
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
            'test_password'
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
            'test_password'
        );

        // Act
        $response = AdminAPI::get()->connection('1')->saveConnectionData($request);

        // Assert
        self::assertEquals([], $response->toArray());
    }

    /**
     * @return void
     *
     * @throws InvalidEnvironmentException
     */
    public function testIsSavingOnboardingDataResponseSuccessful(): void
    {
        // Arrange
        $request = new OnboardingRequest(
            BaseProxy::TEST_MODE,
            'test_username',
            'test_password',
            true,
            'logeecom'
        );

        // Act
        $response = AdminAPI::get()->connection('1')->saveOnboardingData($request);

        // Assert
        self::assertTrue($response->isSuccessful());
    }

    /**
     * @return void
     *
     * @throws InvalidEnvironmentException
     */
    public function testSavingOnboardingDataResponse(): void
    {
        // Arrange
        $request = new OnboardingRequest(
            BaseProxy::TEST_MODE,
            'test_username',
            'test_password',
            true,
            'logeecom'
        );

        // Act
        $response = AdminAPI::get()->connection('1')->saveOnboardingData($request);
        $expectedResponse = new SuccessfulOnboardingResponse();

        // Assert
        self::assertEquals($expectedResponse, $response);
    }

    /**
     * @return void
     *
     * @throws InvalidEnvironmentException
     */
    public function testSavingOnboardingDataResponseToArray(): void
    {
        // Arrange
        $request = new OnboardingRequest(
            BaseProxy::TEST_MODE,
            'test_username',
            'test_password',
            true,
            'logeecom'
        );

        // Act
        $response = AdminAPI::get()->connection('1')->saveOnboardingData($request);

        // Assert
        self::assertEquals([], $response->toArray());
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
            new AuthorizationCredentials('test_username', 'test_password')
        );

        StoreContext::doWithStore('1', [$this->connectionDataRepository,'setConnectionData'], [$connectionData]);

        $request = new ConnectionRequest(
            BaseProxy::TEST_MODE,
            'logeecom2',
            'test_username2',
            'test_password2'
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
            new AuthorizationCredentials('test_username', 'test_password')
        );

        StoreContext::doWithStore('1', [$this->connectionDataRepository,'setConnectionData'], [$connectionData]);

        $request = new ConnectionRequest(
            BaseProxy::TEST_MODE,
            'logeecom2',
            'test_username2',
            'test_password2'
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
            new AuthorizationCredentials('test_username', 'test_password')
        );

        StoreContext::doWithStore('1', [$this->connectionDataRepository,'setConnectionData'], [$connectionData]);

        $request = new ConnectionRequest(
            BaseProxy::TEST_MODE,
            'logeecom2',
            'test_username2',
            'test_password2'
        );

        // Act
        $response = AdminAPI::get()->connection('1')->saveConnectionData($request);

        // Assert
        self::assertEquals([], $response->toArray());
    }

    /**
     * @throws Exception
     */
    public function testIsUpdateOnboardingDataResponseSuccessful(): void
    {
        // Arrange
        $statisticalData = new StatisticalData(true);
        $connectionData = new ConnectionData(
            BaseProxy::TEST_MODE,
            'logeecom',
            new AuthorizationCredentials('test_username', 'test_password')
        );

        StoreContext::doWithStore('1', [$this->statisticalDataRepository,'setStatisticalData'], [$statisticalData]);
        StoreContext::doWithStore('1', [$this->connectionDataRepository,'setConnectionData'], [$connectionData]);

        $request = new OnboardingRequest(
            BaseProxy::TEST_MODE,
            'test_username',
            'test_password',
            true,
            'logeecom'
        );

        // Act
        $response = AdminAPI::get()->connection('1')->saveOnboardingData($request);

        // Assert
        self::assertTrue($response->isSuccessful());
    }

    /**
     * @throws Exception
     */
    public function testUpdateOnboardingDataResponse(): void
    {
        // Arrange
        $statisticalData = new StatisticalData(true);
        $connectionData = new ConnectionData(
            BaseProxy::TEST_MODE,
            'logeecom',
            new AuthorizationCredentials('test_username', 'test_password')
        );

        StoreContext::doWithStore('1', [$this->statisticalDataRepository,'setStatisticalData'], [$statisticalData]);
        StoreContext::doWithStore('1', [$this->connectionDataRepository,'setConnectionData'], [$connectionData]);

        $request = new OnboardingRequest(
            BaseProxy::TEST_MODE,
            'test_username',
            'test_password',
            true,
            'logeecom'
        );

        // Act
        $response = AdminAPI::get()->connection('1')->saveOnboardingData($request);
        $expectedResponse = new SuccessfulOnboardingResponse();

        // Assert
        self::assertEquals($expectedResponse, $response);
    }

    /**
     * @throws Exception
     */
    public function testUpdateOnboardingDataResponseToArray(): void
    {
        // Arrange
        $statisticalData = new StatisticalData(true);
        $connectionData = new ConnectionData(
            BaseProxy::TEST_MODE,
            'logeecom',
            new AuthorizationCredentials('test_username', 'test_password')
        );

        StoreContext::doWithStore('1', [$this->statisticalDataRepository,'setStatisticalData'], [$statisticalData]);
        StoreContext::doWithStore('1', [$this->connectionDataRepository,'setConnectionData'], [$connectionData]);

        $request = new OnboardingRequest(
            BaseProxy::TEST_MODE,
            'test_username',
            'test_password',
            true,
            'logeecom'
        );

        // Act
        $response = AdminAPI::get()->connection('1')->saveOnboardingData($request);

        // Assert
        self::assertEquals([], $response->toArray());
    }

    /**
     * @throws InvalidEnvironmentException
     */
    public function testIsGetConnectionDataResponseSuccessful(): void
    {
        // Arrange
        $this->connectionDataRepository->setConnectionData(
            new ConnectionData(
                BaseProxy::TEST_MODE,
                'logeecom',
                new AuthorizationCredentials('test_username', 'test_password')
            )
        );

        // Act
        $response = AdminAPI::get()->connection('1')->getConnectionSettings();

        // Assert
        self::assertTrue($response->isSuccessful());
    }


    /**
     * @throws Exception
     */
    public function testGetConnectionDataResponse(): void
    {
        // Arrange
        $connectionData = new ConnectionData(
            BaseProxy::TEST_MODE,
            'logeecom',
            new AuthorizationCredentials('test_username', 'test_password')
        );

        StoreContext::doWithStore('1', [$this->connectionDataRepository,'setConnectionData'], [$connectionData]);
        $expectedResponse = new ConnectionSettingsResponse($connectionData);

        // Act
        $response = AdminAPI::get()->connection('1')->getConnectionSettings();

        // Assert
        self::assertEquals($expectedResponse, $response);
    }

    /**
     * @throws Exception
     */
    public function testGetConnectionDataResponseToArray(): void
    {
        // Arrange
        $connectionData = new ConnectionData(
            BaseProxy::TEST_MODE,
            'logeecom',
            new AuthorizationCredentials('test_username', 'test_password')
        );

        StoreContext::doWithStore('1', [$this->connectionDataRepository,'setConnectionData'], [$connectionData]);

        // Act
        $response = AdminAPI::get()->connection('1')->getConnectionSettings();

        // Assert
        self::assertEquals($this->expectedConnectionDataToArrayResponse(), $response->toArray());
    }

    /**
     * @throws Exception
     */
    public function testGetNonExistingConnectionDataResponseToArray(): void
    {
        // Act
        $response = AdminAPI::get()->connection('1')->getConnectionSettings();

        // Assert
        self::assertEquals([], $response->toArray());
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
        $connectionData = new ConnectionData(
            BaseProxy::TEST_MODE,
            'logeecom',
            new AuthorizationCredentials('test_username', 'test_password')
        );

        StoreContext::doWithStore('1', [$this->statisticalDataRepository,'setStatisticalData'], [$statisticalData]);
        StoreContext::doWithStore('1', [$this->connectionDataRepository,'setConnectionData'], [$connectionData]);
        $expectedResponse = new OnboardingDataResponse($connectionData, $statisticalData);

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
        $connectionData = new ConnectionData(
            BaseProxy::TEST_MODE,
            'logeecom',
            new AuthorizationCredentials('test_username', 'test_password')
        );

        StoreContext::doWithStore('1', [$this->statisticalDataRepository,'setStatisticalData'], [$statisticalData]);
        StoreContext::doWithStore('1', [$this->connectionDataRepository,'setConnectionData'], [$connectionData]);

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

    private function expectedConnectionDataToArrayResponse(): array
    {
        return [
            'environment' => 'sandbox',
            'username' => 'test_username',
            'password' => 'test_password',
            'merchantId' => 'logeecom'
        ];
    }

    private function expectedOnboardingDataToArrayResponse(): array
    {
        return [
            'environment' => 'sandbox',
            'username' => 'test_username',
            'password' => 'test_password',
            'merchantId' => 'logeecom',
            'sendStatisticalData' => true
        ];
    }
}
