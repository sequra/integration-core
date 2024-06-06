<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\Connection\Services;

use SeQura\Core\BusinessLogic\Domain\Connection\Models\ConnectionData as DomainConnectionData;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\BadMerchantIdException;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidEnvironmentException;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\WrongCredentialsException;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\AuthorizationCredentials;
use SeQura\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use SeQura\Core\BusinessLogic\SeQuraAPI\BaseProxy;
use SeQura\Core\Infrastructure\Http\Exceptions\HttpRequestException;
use SeQura\Core\Infrastructure\Http\HttpClient;
use SeQura\Core\Infrastructure\Http\HttpResponse;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
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

    protected function setUp(): void
    {
        parent::setUp();

        $httpClient = TestServiceRegister::getService(HttpClient::class);
        $this->httpClient = $httpClient;
        TestServiceRegister::registerService(HttpClient::class, function () {
            return $this->httpClient;
        });

        $this->connectionService = TestServiceRegister::getService(ConnectionService::class);
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
                __DIR__ . '/../../../Common/ApiResponses/Merchant/GetPaymentMethodsResponses/SuccessfulResponse.json'
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
}
