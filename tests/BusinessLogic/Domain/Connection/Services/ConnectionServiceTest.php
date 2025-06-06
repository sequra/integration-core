<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\Connection\Services;

use SeQura\Core\BusinessLogic\Domain\Connection\Models\ConnectionData as DomainConnectionData;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\BadMerchantIdException;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidEnvironmentException;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\WrongCredentialsException;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\AuthorizationCredentials;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\Credentials;
use SeQura\Core\BusinessLogic\Domain\Connection\ProxyContracts\ConnectionProxyInterface;
use SeQura\Core\BusinessLogic\Domain\Connection\RepositoryContracts\CredentialsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use SeQura\Core\BusinessLogic\SeQuraAPI\BaseProxy;
use SeQura\Core\Infrastructure\Http\Exceptions\HttpRequestException;
use SeQura\Core\Infrastructure\Http\HttpClient;
use SeQura\Core\Infrastructure\Http\HttpResponse;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockConnectionProxy;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockCredentialsRepository;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TestHttpClient;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class ConnectionServiceTest
 *
 * @package SeQura\Core\Tests\BusinessLogic\Domain\Connection\Services
 */
class ConnectionServiceTest extends BaseTestCase
{
    /**
     * @var ConnectionService
     */
    private $connectionService;
    /**
     * @var TestHttpClient
     */
    public $httpClient;

    /**
     * @var MockConnectionProxy $mockConnectionProxy
     */
    private $mockConnectionProxy;

    /**
     * @var MockCredentialsRepository $mockCredentialsRepository
     */
    private $mockCredentialsRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $httpClient = TestServiceRegister::getService(HttpClient::class);
        $this->httpClient = $httpClient;
        TestServiceRegister::registerService(HttpClient::class, function () {
            return $this->httpClient;
        });

        $this->connectionService = TestServiceRegister::getService(ConnectionService::class);
        $this->mockConnectionProxy = new MockConnectionProxy();
        $this->mockCredentialsRepository = TestServiceRegister::getService(CredentialsRepositoryInterface::class);
    }

    /**
     * @throws InvalidEnvironmentException
     * @throws BadMerchantIdException
     * @throws WrongCredentialsException
     * @throws HttpRequestException
     */
    public function testConnectionValidation(): void
    {
        $connectionData = new DomainConnectionData(
            BaseProxy::TEST_MODE,
            'logeecom',
            new AuthorizationCredentials('test_username', 'test_password')
        );

        $this->httpClient->setMockResponses([
            new HttpResponse(200, [], file_get_contents(
                __DIR__ . '/../../../Common/ApiResponses/Connection/SuccessfulResponse.json'
            ))
        ]);

        $isValid = $this->connectionService->isConnectionDataValid($connectionData);
        self::assertTrue($isValid);
    }

    /**
     * @throws BadMerchantIdException
     * @throws InvalidEnvironmentException
     * @throws HttpRequestException
     */
    public function testConnectionValidationWithInvalidCredentials(): void
    {
        $connectionData = new DomainConnectionData(
            BaseProxy::TEST_MODE,
            'test_merchant',
            new AuthorizationCredentials('wrong_username', 'wrong_password')
        );

        $this->httpClient->setMockResponses([
            new HttpResponse(401, [], file_get_contents(
                __DIR__ . '/../../../Common/ApiResponses/Merchant/GetPaymentMethodsResponses/WrongCredentialsResponse.txt'
            ))
        ]);

        $this->expectException(WrongCredentialsException::class);

        $this->connectionService->isConnectionDataValid($connectionData);
    }

    /**
     * @throws WrongCredentialsException
     * @throws InvalidEnvironmentException
     * @throws HttpRequestException
     */
    public function testConnectionValidationWithInvalidMerchantId(): void
    {
        $connectionData = new DomainConnectionData(
            BaseProxy::TEST_MODE,
            'test_merchant',
            new AuthorizationCredentials('test_username', 'test_password')
        );

        $this->httpClient->setMockResponses([
            new HttpResponse(403, [], file_get_contents(
                __DIR__ . '/../../../Common/ApiResponses/Merchant/GetPaymentMethodsResponses/InvalidMerchantIdResponse.json'
            ))
        ]);

        $this->expectException(BadMerchantIdException::class);

        $this->connectionService->isConnectionDataValid($connectionData);
    }

    /**
     * @throws InvalidEnvironmentException
     */
    public function testSaveAndGetConnectionData(): void
    {
        $connectionData = new DomainConnectionData(
            BaseProxy::TEST_MODE,
            'test_merchant',
            new AuthorizationCredentials('test_username', 'test_password')
        );

        $this->connectionService->saveConnectionData($connectionData);
        $result = $this->connectionService->getConnectionData();

        $this->assertEquals($connectionData->getAuthorizationCredentials(), $result->getAuthorizationCredentials());
        $this->assertEquals($connectionData->getMerchantId(), $result->getMerchantId());
        $this->assertEquals($connectionData->getEnvironment(), $result->getEnvironment());
    }

    /**
     * @return void
     *
     * @throws BadMerchantIdException
     * @throws HttpRequestException
     * @throws InvalidEnvironmentException
     * @throws WrongCredentialsException
     */
    public function testConnectWrongCredentials(): void
    {
        //Arrange
        TestServiceRegister::registerService(ConnectionProxyInterface::class, function () {
            return $this->mockConnectionProxy;
        });
        $this->connectionService = TestServiceRegister::getService(ConnectionService::class);
        $connectionData = new DomainConnectionData(
            BaseProxy::TEST_MODE,
            'test_merchant',
            new AuthorizationCredentials('test_username', 'test_password')
        );
        $this->mockConnectionProxy->setWrongCredentials(true);
        $this->expectException(WrongCredentialsException::class);

        //Act
        $this->connectionService->connect($connectionData);

        //Assert
    }

    /**
     * @return void
     *
     * @throws BadMerchantIdException
     * @throws HttpRequestException
     * @throws InvalidEnvironmentException
     * @throws WrongCredentialsException
     */
    public function testBadMerchantIdException(): void
    {
        //Arrange
        TestServiceRegister::registerService(ConnectionProxyInterface::class, function () {
            return $this->mockConnectionProxy;
        });
        $this->connectionService = TestServiceRegister::getService(ConnectionService::class);
        $connectionData = new DomainConnectionData(
            BaseProxy::TEST_MODE,
            'test_merchant',
            new AuthorizationCredentials('test_username', 'test_password')
        );
        $this->mockConnectionProxy->setBadMerchantId(true);
        $this->expectException(BadMerchantIdException::class);

        //Act
        $this->connectionService->connect($connectionData);

        //Assert
    }

    /**
     * @return void
     *
     * @throws BadMerchantIdException
     * @throws HttpRequestException
     * @throws InvalidEnvironmentException
     * @throws WrongCredentialsException
     */
    public function testConnectCredentialsSaved(): void
    {
        //Arrange
        TestServiceRegister::registerService(ConnectionProxyInterface::class, function () {
            return $this->mockConnectionProxy;
        });
        $this->connectionService = TestServiceRegister::getService(ConnectionService::class);
        $connectionData = new DomainConnectionData(
            BaseProxy::TEST_MODE,
            'test_merchant',
            new AuthorizationCredentials('test_username', 'test_password')
        );

        $credentials = [
            new Credentials('logeecom1', 'PT', 'EUR', 'assetsKey1', []),
            new Credentials('logeecom2', 'FR', 'EUR', 'assetsKey2', []),
            new Credentials('logeecom3', 'IT', 'EUR', 'assetsKey3', []),
            new Credentials('logeecom4', 'ES', 'EUR', 'assetsKey4', [])
        ];
        $this->mockConnectionProxy->setMockCredentials($credentials);

        //Act
        $this->connectionService->connect($connectionData);

        //Assert
        $savedCredentials = $this->mockCredentialsRepository->getCredentials();

        self::assertEquals($credentials, $savedCredentials);
    }

    /**
     * @return void
     *
     * @throws BadMerchantIdException
     * @throws HttpRequestException
     * @throws InvalidEnvironmentException
     * @throws WrongCredentialsException
     */
    public function testConnectConnectionDataSaved(): void
    {
        //Arrange
        TestServiceRegister::registerService(ConnectionProxyInterface::class, function () {
            return $this->mockConnectionProxy;
        });
        $this->connectionService = TestServiceRegister::getService(ConnectionService::class);
        $connectionData = new DomainConnectionData(
            BaseProxy::TEST_MODE,
            'test_merchant',
            new AuthorizationCredentials('test_username', 'test_password')
        );

        $credentials = [
            new Credentials('logeecom1', 'PT', 'EUR', 'assetsKey1', []),
            new Credentials('logeecom2', 'FR', 'EUR', 'assetsKey2', []),
            new Credentials('logeecom3', 'IT', 'EUR', 'assetsKey3', []),
            new Credentials('logeecom4', 'ES', 'EUR', 'assetsKey4', [])
        ];
        $this->mockConnectionProxy->setMockCredentials($credentials);

        //Act
        $this->connectionService->connect($connectionData);

        //Assert
        $result = $this->connectionService->getConnectionData();

        self::assertEquals($connectionData->getAuthorizationCredentials(), $result->getAuthorizationCredentials());
        self::assertEquals($connectionData->getMerchantId(), $result->getMerchantId());
        self::assertEquals($connectionData->getEnvironment(), $result->getEnvironment());
    }

    /**
     * @throws WrongCredentialsException
     * @throws BadMerchantIdException
     * @throws HttpRequestException
     */
    public function testGetCredentialsWithValidationEmptyArray(): void
    {
        //Arrange
        TestServiceRegister::registerService(ConnectionProxyInterface::class, function () {
            return $this->mockConnectionProxy;
        });
        $this->connectionService = TestServiceRegister::getService(ConnectionService::class);

        $credentials = [
            new Credentials('logeecom1', 'PT', 'EUR', 'assetsKey1', []),
            new Credentials('logeecom2', 'FR', 'EUR', 'assetsKey2', []),
            new Credentials('logeecom3', 'IT', 'EUR', 'assetsKey3', []),
            new Credentials('logeecom4', 'ES', 'EUR', 'assetsKey4', [])
        ];
        $this->mockConnectionProxy->setMockCredentials($credentials);
        $this->mockCredentialsRepository->setCredentials([]);

        //Act
        $returnedCredentials = $this->connectionService->getCredentials();

        //Assert
        self::assertEmpty($returnedCredentials);
    }

    /**
     * @throws WrongCredentialsException
     * @throws BadMerchantIdException
     * @throws HttpRequestException
     * @throws InvalidEnvironmentException
     */
    public function testGetCredentialsWithValidation(): void
    {
        //Arrange
        TestServiceRegister::registerService(ConnectionProxyInterface::class, function () {
            return $this->mockConnectionProxy;
        });
        $this->connectionService = TestServiceRegister::getService(ConnectionService::class);
        $connectionData = new DomainConnectionData(
            BaseProxy::TEST_MODE,
            'test_merchant',
            new AuthorizationCredentials('test_username', 'test_password')
        );
        $this->connectionService->saveConnectionData($connectionData);

        $credentials = [
            new Credentials('logeecom1', 'PT', 'EUR', 'assetsKey1', []),
            new Credentials('logeecom2', 'FR', 'EUR', 'assetsKey2', []),
            new Credentials('logeecom3', 'IT', 'EUR', 'assetsKey3', []),
            new Credentials('logeecom4', 'ES', 'EUR', 'assetsKey4', [])
        ];
        $this->mockConnectionProxy->setMockCredentials($credentials);
        $this->mockCredentialsRepository->setCredentials([]);

        //Act
        $returnedCredentials = $this->connectionService->getCredentials();

        //Assert
        self::assertEquals($returnedCredentials, $credentials);
    }
}
