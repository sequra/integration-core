<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\Connection\Services;

use DateTime;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionDataNotFoundException;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\CredentialsNotFoundException;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\ConnectionData as DomainConnectionData;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\BadMerchantIdException;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidEnvironmentException;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\WrongCredentialsException;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\AuthorizationCredentials;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\Credentials;
use SeQura\Core\BusinessLogic\Domain\Connection\ProxyContracts\ConnectionProxyInterface;
use SeQura\Core\BusinessLogic\Domain\Connection\RepositoryContracts\ConnectionDataRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Connection\RepositoryContracts\CredentialsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use SeQura\Core\BusinessLogic\Domain\Connection\Services\CredentialsService;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Models\CountryConfiguration;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\RepositoryContracts\CountryConfigurationRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Exceptions\PaymentMethodNotFoundException;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Models\SeQuraCost;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Models\SeQuraPaymentMethod;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\RepositoryContracts\PaymentMethodRepositoryInterface;
use SeQura\Core\BusinessLogic\SeQuraAPI\BaseProxy;
use SeQura\Core\Infrastructure\Http\Exceptions\HttpRequestException;
use SeQura\Core\Infrastructure\Http\HttpClient;
use SeQura\Core\Infrastructure\Http\HttpResponse;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockConnectionProxy;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockCountryConfigurationRepository;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockCredentialsRepository;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockPaymentMethodRepository;
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

    /**
     * @var MockCountryConfigurationRepository
     */
    private $mockCountryConfigurationRepository;

    /**
     * @var MockPaymentMethodRepository
     */
    private $mockPaymentMethodRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $httpClient = TestServiceRegister::getService(HttpClient::class);
        $this->httpClient = $httpClient;
        TestServiceRegister::registerService(HttpClient::class, function () {
            return $this->httpClient;
        });
        $this->mockConnectionProxy = new MockConnectionProxy();
        TestServiceRegister::registerService(ConnectionProxyInterface::class, function () {
            return $this->mockConnectionProxy;
        });

        $this->connectionService = new ConnectionService(
            TestServiceRegister::getService(ConnectionDataRepositoryInterface::class),
            TestServiceRegister::getService(CredentialsService::class)
        );

        $this->mockCredentialsRepository = TestServiceRegister::getService(CredentialsRepositoryInterface::class);
        $this->mockCountryConfigurationRepository = TestServiceRegister::getService(CountryConfigurationRepositoryInterface::class);
        $this->mockPaymentMethodRepository = TestServiceRegister::getService(PaymentMethodRepositoryInterface::class);
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
            'sequra',
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
            'sequra',
            new AuthorizationCredentials('wrong_username', 'wrong_password')
        );
        $this->mockConnectionProxy->setWrongCredentials(true);
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
            'sequra',
            new AuthorizationCredentials('test_username', 'test_password')
        );

        $this->mockConnectionProxy->setBadMerchantId(true);
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
            'sequra',
            new AuthorizationCredentials('test_username', 'test_password')
        );

        $this->connectionService->saveConnectionData($connectionData);
        $result = $this->connectionService->getConnectionDataByDeployment('sequra');

        $this->assertEquals($connectionData->getAuthorizationCredentials(), $result->getAuthorizationCredentials());
        $this->assertEquals($connectionData->getMerchantId(), $result->getMerchantId());
        $this->assertEquals($connectionData->getEnvironment(), $result->getEnvironment());
        $this->assertEquals($connectionData->getDeployment(), $result->getDeployment());
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

        $connectionData = new DomainConnectionData(
            BaseProxy::TEST_MODE,
            'test_merchant',
            'sequra',
            new AuthorizationCredentials('test_username', 'test_password')
        );
        $this->mockConnectionProxy->setWrongCredentials(true);
        $this->expectException(WrongCredentialsException::class);

        //Act
        $this->connectionService->connect([$connectionData]);

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
        $connectionData = new DomainConnectionData(
            BaseProxy::TEST_MODE,
            'test_merchant',
            'sequra',
            new AuthorizationCredentials('test_username', 'test_password')
        );
        $this->mockConnectionProxy->setBadMerchantId(true);
        $this->expectException(BadMerchantIdException::class);

        //Act
        $this->connectionService->connect([$connectionData]);

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
        $connectionData = new DomainConnectionData(
            BaseProxy::TEST_MODE,
            'test_merchant',
            'sequra',
            new AuthorizationCredentials('test_username', 'test_password')
        );

        $credentials = [
            new Credentials('logeecom1', 'PT', 'EUR', 'assetsKey1', [], 'sequra'),
            new Credentials('logeecom2', 'FR', 'EUR', 'assetsKey2', [], 'sequra'),
            new Credentials('logeecom3', 'IT', 'EUR', 'assetsKey3', [], 'svea'),
            new Credentials('logeecom4', 'ES', 'EUR', 'assetsKey4', [], 'svea')
        ];
        $this->mockConnectionProxy->setMockCredentials($credentials);

        //Act
        $this->connectionService->connect([$connectionData]);

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
        $connectionData = new DomainConnectionData(
            BaseProxy::TEST_MODE,
            'test_merchant',
            'sequra',
            new AuthorizationCredentials('test_username', 'test_password')
        );

        $credentials = [
            new Credentials('logeecom1', 'PT', 'EUR', 'assetsKey1', [], 'sequra'),
            new Credentials('logeecom2', 'FR', 'EUR', 'assetsKey2', [], 'sequra'),
            new Credentials('logeecom3', 'IT', 'EUR', 'assetsKey3', [], 'svea'),
            new Credentials('logeecom4', 'ES', 'EUR', 'assetsKey4', [], 'svea')
        ];
        $this->mockConnectionProxy->setMockCredentials($credentials);

        //Act
        $this->connectionService->connect([$connectionData]);

        //Assert
        $result = $this->connectionService->getConnectionDataByDeployment('sequra');

        self::assertEquals($connectionData->getAuthorizationCredentials(), $result->getAuthorizationCredentials());
        self::assertEquals($connectionData->getMerchantId(), $result->getMerchantId());
        self::assertEquals($connectionData->getEnvironment(), $result->getEnvironment());
        self::assertEquals($connectionData->getDeployment(), $result->getDeployment());
    }

    /**
     * @throws WrongCredentialsException
     * @throws BadMerchantIdException
     * @throws HttpRequestException
     */
    public function testGetCredentialsWithValidationEmptyArray(): void
    {
        //Arrange
        $credentials = [
            new Credentials('logeecom1', 'PT', 'EUR', 'assetsKey1', [], 'sequra'),
            new Credentials('logeecom2', 'FR', 'EUR', 'assetsKey2', [], 'sequra'),
            new Credentials('logeecom3', 'IT', 'EUR', 'assetsKey3', [], 'svea'),
            new Credentials('logeecom4', 'ES', 'EUR', 'assetsKey4', [], 'svea')
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
        $connectionData = new DomainConnectionData(
            BaseProxy::TEST_MODE,
            'test_merchant',
            'sequra',
            new AuthorizationCredentials('test_username', 'test_password')
        );
        $this->connectionService->saveConnectionData($connectionData);

        $credentials = [
            new Credentials('logeecom1', 'PT', 'EUR', 'assetsKey1', [], 'sequra'),
            new Credentials('logeecom2', 'FR', 'EUR', 'assetsKey2', [], 'sequra'),
            new Credentials('logeecom3', 'IT', 'EUR', 'assetsKey3', [], 'svea'),
            new Credentials('logeecom4', 'ES', 'EUR', 'assetsKey4', [], 'svea')
        ];
        $this->mockConnectionProxy->setMockCredentials($credentials);
        $this->mockCredentialsRepository->setCredentials([]);

        //Act
        $returnedCredentials = $this->connectionService->getCredentials();

        //Assert
        self::assertEquals($returnedCredentials, $credentials);
    }

    /**
     * @return void
     *
     * @throws ConnectionDataNotFoundException
     * @throws CredentialsNotFoundException
     * @throws InvalidEnvironmentException
     */
    public function testGetConnectionDataByMerchantIdNoCredentials(): void
    {
        //Arrange
        $this->connectionService = TestServiceRegister::getService(ConnectionService::class);
        $connectionData = new DomainConnectionData(
            BaseProxy::TEST_MODE,
            'test_merchant',
            'sequra',
            new AuthorizationCredentials('test_username', 'test_password')
        );
        $this->connectionService->saveConnectionData($connectionData);
        $this->mockCredentialsRepository->setCredentials([
            new Credentials('test_merchant', 'PT', 'EUR', 'assetsKey1', [], 'sequra'),
        ]);

        //Act
        $result = $this->connectionService->getConnectionDataByMerchantId('test_merchant');

        //Assert
        self::assertEquals($result, $connectionData);
    }

    /**
     * @return void
     *
     * @throws CredentialsNotFoundException
     * @throws InvalidEnvironmentException
     * @throws ConnectionDataNotFoundException
     */
    public function testGetConnectionDataByMerchantIdCredentialsNotFound(): void
    {
        $connectionData = new DomainConnectionData(
            BaseProxy::TEST_MODE,
            'test_merchant',
            'sequra',
            new AuthorizationCredentials('test_username', 'test_password')
        );
        $this->connectionService->saveConnectionData($connectionData);
        $this->mockCredentialsRepository->setCredentials([]);
        $this->expectException(CredentialsNotFoundException::class);

        //Act
        $this->connectionService->getConnectionDataByMerchantId('test_merchant');

        //Assert
    }

    /**
     * @return void
     *
     * @throws CredentialsNotFoundException
     * @throws ConnectionDataNotFoundException
     */
    public function testGetConnectionDataByMerchantIdConnectionDataNotFoundException(): void
    {
        $this->mockCredentialsRepository->setCredentials([
            new Credentials('test_merchant', 'PT', 'EUR', 'assetsKey1', [], 'sequra')
        ]);
        $this->mockCredentialsRepository->setCredentials([
            new Credentials('test_merchant', 'PT', 'EUR', 'assetsKey1', [], 'sequra'),
        ]);

        $this->expectException(ConnectionDataNotFoundException::class);

        //Act
        $this->connectionService->getConnectionDataByMerchantId('test_merchant');

        //Assert
    }

    /**
     * @return void
     *
     * @throws CredentialsNotFoundException
     * @throws InvalidEnvironmentException
     * @throws ConnectionDataNotFoundException
     */
    public function testGetConnectionDataByMerchantId(): void
    {
        $connectionData = new DomainConnectionData(
            BaseProxy::TEST_MODE,
            'test_merchant',
            'sequra',
            new AuthorizationCredentials('test_username', 'test_password')
        );
        $this->connectionService->saveConnectionData($connectionData);
        $this->mockCredentialsRepository->setCredentials([
            new Credentials('test_merchant', 'PT', 'EUR', 'assetsKey1', [], 'sequra')
        ]);

        //Act
        $result = $this->connectionService->getConnectionDataByMerchantId('test_merchant');

        //Assert
        self::assertEquals($connectionData, $result);
    }

    /**
     * @return void
     *
     * @throws BadMerchantIdException
     * @throws HttpRequestException
     * @throws InvalidEnvironmentException
     * @throws WrongCredentialsException
     * @throws PaymentMethodNotFoundException
     */
    public function testConnectNewDeploymentSuccessful(): void
    {
        //Arrange
        $connectionData = new DomainConnectionData(
            BaseProxy::TEST_MODE,
            '',
            'sequra',
            new AuthorizationCredentials('test_username', 'test_password')
        );
        $this->connectionService->saveConnectionData($connectionData);

        $credentials = [
            new Credentials('logeecom1', 'PT', 'EUR', 'assetsKey1', [], 'sequra'),
            new Credentials('logeecom2', 'FR', 'EUR', 'assetsKey2', [], 'sequra')
        ];
        $this->mockCredentialsRepository->setCredentials($credentials);

        $countryConfigurations = [
            new CountryConfiguration(
                'PT',
                'logeecom1'
            )
        ];
        $this->mockCountryConfigurationRepository->setCountryConfiguration($countryConfigurations);

        $paymentMethod = new SeQuraPaymentMethod(
            'i1',
            'Paga Después',
            'Paga después. 7 días desde el envío',
            'pay_later',
            new SeQuraCost(0, 0, 0, 0),
            new DateTime('2000-02-22T21:22:00Z'),
            new DateTime('2222-02-22T21:22:00Z'),
            null,
            'Sin coste adicional',
            'Compra ahora, recibe primero y paga después. Cuando tu pedido salga de la tienda tendrás 7 días para realizar el pago desde el enlace que recibirás en tu email o mediante transferencia bancaria.',
            '<svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 129 56" height="40" style="enable-background:new 0 0 129 56;" xml:space="preserve"><style type="text/css">.st0{fill-rule:evenodd;clip-rule:evenodd;fill:#FFFFFF;}</style><path d="M8.2,0h112.6c4.5,0,8.2,3.7,8.2,8.2v39.6c0,4.5-3.7,8.2-8.2,8.2H8.2C3.7,56,0,52.3,0,47.8V8.2C0,3.7,3.7,0,8.2,0z"/><g><g><path class="st0" d="M69.3,36.5c-0.7,0-1.4-0.1-2-0.3c1.3-1.5,2.2-3.4,2.7-5.4c0.7-3,0.2-6.1-1.2-8.7c-1.4-2.7-3.8-4.8-6.6-5.9c-1.5-0.6-3.1-0.9-4.8-0.9c-1.4,0-2.8,0.2-4.1,0.7c-2.9,1-5.3,2.9-6.9,5.5c-1.6,2.6-2.2,5.7-1.7,8.7c0.5,3,2,5.7,4.4,7.7c2.2,1.9,5.1,3,8,3c0.3,0,0.6-0.1,0.8-0.4c0.2-0.2,0.2-0.4,0.2-0.7v-2.1c0-0.6-0.5-1.1-1.1-1.1c-1.9-0.1-3.8-0.8-5.2-2c-1.5-1.3-2.6-3.1-2.9-5.1c-0.3-2,0.1-4,1.1-5.7c1-1.7,2.7-3,4.6-3.7c0.9-0.3,1.8-0.4,2.7-0.4c1.1,0,2.1,0.2,3.1,0.6c1.9,0.7,3.4,2.1,4.4,3.9c1,1.8,1.2,3.8,0.8,5.8c-0.3,1.5-1.1,2.9-2.2,4.1c-0.7-0.7-1.3-1.6-1.8-2.6c-0.4-0.9-0.6-1.9-0.6-2.9c0-0.6-0.5-1.1-1.1-1.1h-2.1c-0.3,0-0.6,0.1-0.7,0.3c-0.2,0.2-0.4,0.5-0.4,0.8c0,1.6,0.4,3.1,1,4.6c0.6,1.5,1.6,2.9,2.8,4.1c1.2,1.2,2.6,2.1,4.2,2.8c1.5,0.6,3,0.9,4.6,1h0c0.3,0,0.6-0.1,0.8-0.4c0.2-0.2,0.2-0.4,0.2-0.7v-2.1c0-0.3-0.1-0.6-0.3-0.8C69.9,36.6,69.6,36.5,69.3,36.5z"/></g><g><path class="st0" d="M21.1,29c-0.6-0.5-1.3-0.8-2-1c-0.7-0.3-1.5-0.5-2.3-0.7c-0.6-0.1-1.1-0.3-1.6-0.4c-0.5-0.1-0.9-0.3-1.3-0.5l-0.1,0c-0.1-0.1-0.3-0.1-0.4-0.2c-0.1-0.1-0.2-0.2-0.3-0.2c0-0.1-0.1-0.1-0.1-0.1c0-0.1,0-0.1,0-0.2c0-0.2,0.1-0.3,0.2-0.4c0.1-0.2,0.3-0.3,0.6-0.4c0.3-0.1,0.6-0.2,0.9-0.3c0.2,0,0.5-0.1,0.7-0.1c0.1,0,0.2,0,0.4,0c0.6,0,1.1,0.2,1.6,0.4c0.3,0.2,1,0.7,1.6,1.2c0.1,0.1,0.3,0.2,0.5,0.2c0.2,0,0.3,0,0.4-0.1l2.2-1.5c0.2-0.1,0.3-0.3,0.3-0.5c0-0.2,0-0.4-0.1-0.6c-0.6-0.8-1.4-1.5-2.3-2c-1.1-0.6-2.4-0.9-3.8-1c-0.3,0-0.5,0-0.8,0c-0.6,0-1.2,0.1-1.8,0.2c-0.8,0.1-1.6,0.4-2.4,0.8c-0.7,0.3-1.4,0.9-1.9,1.5c-0.5,0.7-0.8,1.5-0.9,2.3c-0.1,0.8,0.1,1.7,0.5,2.4c0.4,0.6,0.9,1.2,1.5,1.6c0.6,0.4,1.3,0.7,2.1,1c0.9,0.3,1.6,0.5,2.4,0.7c0.4,0.1,0.9,0.2,1.4,0.4c0.4,0.1,0.7,0.3,1,0.4l0.1,0c0.2,0.1,0.4,0.3,0.5,0.5c0.1,0.2,0.1,0.3,0.1,0.6c0,0.2-0.1,0.4-0.2,0.5c-0.2,0.2-0.4,0.3-0.6,0.4h-0.1l-0.1,0c-0.3,0.1-0.7,0.2-1.1,0.3c-0.2,0-0.5,0-0.7,0c-0.2,0-0.3,0-0.5,0c-0.8,0-1.6-0.2-2.2-0.6c-0.5-0.3-1-0.7-1.3-1.1C11.2,32.1,11,32,10.8,32c-0.2,0-0.3,0-0.4,0.1L8,33.8c-0.2,0.1-0.3,0.3-0.3,0.5c0,0.2,0,0.4,0.2,0.6c0.7,0.9,1.6,1.6,2.6,2l0,0l0.1,0c1.3,0.6,2.7,0.9,4.1,0.9c0.3,0,0.7,0,1,0c0.6,0,1.2,0,1.8-0.1c0.9-0.1,1.8-0.4,2.6-0.7c0.8-0.4,1.5-0.9,2-1.6c0.6-0.8,0.9-1.6,0.9-2.6c0.1-0.8-0.1-1.6-0.4-2.3C22.2,30,21.7,29.4,21.1,29z"/></g><g><path class="st0" d="M112.4,20.5c-4.9,0-8.9,3.9-8.9,8.7s4,8.7,8.9,8.7c2.5,0,4-1,4.7-1.7v0.6c0,0.6,0.5,1.1,1.1,1.1h2c0.6,0,1.1-0.5,1.1-1.1v-7.7C121.3,24.4,117.4,20.5,112.4,20.5z M112.4,24.6c2.6,0,4.7,2.1,4.7,4.6s-2.1,4.6-4.7,4.6c-2.6,0-4.7-2.1-4.7-4.6S109.8,24.6,112.4,24.6z"/></g><g><path class="st0" d="M101.3,20.5C101.3,20.5,101.3,20.5,101.3,20.5c-1.1,0-2.1,0.3-3.1,0.7c-1.1,0.4-2.1,1.1-2.9,1.9c-0.8,0.8-1.5,1.8-1.9,2.9c-0.4,1-0.6,2-0.7,3.1v7.9c0,0.6,0.5,1.1,1.1,1.1h2c0.6,0,1.1-0.5,1.1-1.1V29c0-0.5,0.1-1,0.3-1.5c0.2-0.6,0.6-1.1,1-1.5c0.4-0.4,1-0.8,1.5-1c0.5-0.2,1-0.3,1.5-0.3c0.6,0,1.1-0.5,1.1-1.1v-2c0-0.3-0.2-0.6-0.4-0.8C101.9,20.6,101.7,20.5,101.3,20.5z"/></g><g><path class="st0" d="M88.8,20.3h-2c-0.6,0-1.1,0.5-1.1,1.1l0,8.4c-0.1,1.1-0.6,2-1.3,2.7c-0.4,0.4-0.9,0.7-1.4,0.9c-0.5,0.2-1.1,0.3-1.7,0.3s-1.1-0.1-1.7-0.3c-0.5-0.2-1-0.5-1.4-0.9c-0.7-0.7-1.1-1.6-1.3-2.7v-8.4c0-0.6-0.5-1.1-1.1-1.1h-2c-0.6,0-1.1,0.5-1.1,1.1v8.1c0,1.1,0.2,2.2,0.6,3.2c0.4,1,1,1.9,1.8,2.8c0.8,0.8,1.7,1.4,2.8,1.8c1.1,0.4,2.1,0.6,3.3,0.6c1.1,0,2.2-0.2,3.3-0.6c1-0.4,2-1,2.8-1.8c1.4-1.4,2.3-3.2,2.5-5.3l0-8.8C89.9,20.8,89.4,20.3,88.8,20.3z"/></g><g><path class="st0" d="M34.6,20.5c-0.4-0.1-0.8-0.1-1.2-0.1c-1.7,0-3.4,0.5-4.9,1.5c-1.8,1.2-3,3-3.6,5c-0.5,2.1-0.3,4.2,0.6,6.1c0.9,1.9,2.5,3.4,4.5,4.2c1.1,0.4,2.2,0.7,3.3,0.7c1,0,1.9-0.2,2.8-0.5c1.5-0.5,2.8-1.4,3.8-2.6c0.2-0.2,0.3-0.6,0.2-0.9c-0.1-0.3-0.3-0.6-0.6-0.8l-1.6-0.8c-0.2-0.1-0.4-0.1-0.5-0.1c-0.3,0-0.6,0.1-0.8,0.3c-0.5,0.5-1.2,0.9-1.8,1.1c-0.5,0.2-1,0.3-1.6,0.3c-0.6,0-1.3-0.1-1.8-0.4c-1.1-0.5-2-1.3-2.5-2.3c0,0,0-0.1-0.1-0.2h12.1c0.6,0,1.1-0.5,1.1-1.1v-0.9c0-2.1-0.8-4.1-2.2-5.7C38.7,21.8,36.7,20.8,34.6,20.5z M30.8,25.1c0.8-0.5,1.8-0.8,2.7-0.8c0.2,0,0.4,0,0.6,0c1.2,0.2,2.3,0.7,3,1.6c0.4,0.4,0.6,0.8,0.8,1.4h-9.1C29.3,26.4,30,25.6,30.8,25.1z"/></g></g></svg>',
            'sin coste adicional',
            0,
            null
        );
        $this->mockPaymentMethodRepository->setPaymentMethod('logeecom1', $paymentMethod);

        $connectionData2 = new DomainConnectionData(
            BaseProxy::TEST_MODE,
            '',
            'svea',
            new AuthorizationCredentials('test_username2', 'test_password2')
        );

        $credentials2 = [
            new Credentials('logeecom1-svea', 'PT', 'EUR', 'assetsKey1', [], 'svea'),
            new Credentials('logeecom2-svea', 'FR', 'EUR', 'assetsKey2', [], 'svea')
        ];

        $this->mockConnectionProxy->setMockCredentials($credentials2);

        //Act
        $this->connectionService->connect([$connectionData2]);

        $countryConfigurations = $this->mockCountryConfigurationRepository->getCountryConfiguration();
        $paymentMethod = $this->mockPaymentMethodRepository->getPaymentMethods('logeecom1');

        //Assert
        self::assertEmpty($paymentMethod);
        self::assertEquals('logeecom1-svea', $countryConfigurations[0]->getMerchantId());
    }
}
