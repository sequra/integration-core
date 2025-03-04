<?php

namespace SeQura\Core\Tests\BusinessLogic\SeQuraAPI\Connection;

use Exception;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidEnvironmentException;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\AuthorizationCredentials;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\ValidateConnectionRequest;
use SeQura\Core\BusinessLogic\Domain\Connection\ProxyContracts\ConnectionProxyInterface;
use SeQura\Core\BusinessLogic\SeQuraAPI\BaseProxy;
use SeQura\Core\BusinessLogic\SeQuraAPI\Exceptions\HttpApiInvalidUrlParameterException;
use SeQura\Core\BusinessLogic\SeQuraAPI\Exceptions\HttpApiUnauthorizedException;
use SeQura\Core\Infrastructure\Http\Exceptions\HttpRequestException;
use SeQura\Core\Infrastructure\Http\HttpClient;
use SeQura\Core\Infrastructure\Http\HttpResponse;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TestHttpClient;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class ConnectionProxyTest
 *
 * @package SeQura\Core\Tests\BusinessLogic\SeQuraAPI\Connection
 */
class ConnectionProxyTest extends BaseTestCase
{
    /**
     * @var ConnectionProxyInterface
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
     */
    protected function setUp(): void
    {
        parent::setUp();

        $httpClient = TestServiceRegister::getService(HttpClient::class);
        $this->httpClient = $httpClient;
        TestServiceRegister::registerService(HttpClient::class, function () {
            return $this->httpClient;
        });

        $this->proxy = TestServiceRegister::getService(ConnectionProxyInterface::class);
    }

    /**
     * @return void
     *
     * @throws HttpRequestException
     * @throws InvalidEnvironmentException
     * @throws HttpRequestException
     */
    public function testConnectionRequestUrlWithMerchantRef(): void
    {
        $this->httpClient->setMockResponses([
            new HttpResponse(204, [], file_get_contents(
                __DIR__ . '/../../Common/ApiResponses/Connection/SuccessfulResponse.json'
            ))
        ]);

        $connectionData = new ConnectionData(
            BaseProxy::TEST_MODE,
            'test',
            new AuthorizationCredentials('test_username', 'test_password')
        );

        $this->proxy->validateConnection(new ValidateConnectionRequest($connectionData));
        self::assertCount(1, $this->httpClient->getHistory());
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertStringContainsString('merchants/test/credentials', $lastRequest['url']);
    }

    /**
     * @return void
     *
     * @throws HttpRequestException
     * @throws InvalidEnvironmentException
     * @throws HttpRequestException
     */
    public function testConnectionRequestUrlWithoutMerchantRef(): void
    {
        $this->httpClient->setMockResponses([
            new HttpResponse(204, [], file_get_contents(
                __DIR__ . '/../../Common/ApiResponses/Connection/SuccessfulResponse.json'
            ))
        ]);

        $connectionData = new ConnectionData(
            BaseProxy::TEST_MODE,
            null,
            new AuthorizationCredentials('test_username', 'test_password')
        );

        $this->proxy->validateConnection(new ValidateConnectionRequest($connectionData));
        self::assertCount(1, $this->httpClient->getHistory());
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertStringContainsString('merchants/credentials', $lastRequest['url']);
    }

    /**
     * @return void
     *
     * @throws HttpRequestException
     * @throws InvalidEnvironmentException
     */
    public function testConnectionRequestAuthHeader(): void
    {
        $this->httpClient->setMockResponses([
            new HttpResponse(204, [], file_get_contents(
                __DIR__ . '/../../Common/ApiResponses/Connection/SuccessfulResponse.json'
            ))
        ]);

        $connectionData = new ConnectionData(
            BaseProxy::TEST_MODE,
            'test',
            new AuthorizationCredentials('test_username', 'test_password')
        );

        $this->proxy->validateConnection(new ValidateConnectionRequest($connectionData));
        self::assertCount(1, $this->httpClient->getHistory());
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertArrayHasKey('Authorization', $lastRequest['headers']);
    }

    /**
     * @return void
     *
     * @throws HttpRequestException
     * @throws InvalidEnvironmentException
     */
    public function testConnectionRequestMethod(): void
    {
        $this->httpClient->setMockResponses([
            new HttpResponse(204, [], file_get_contents(
                __DIR__ . '/../../Common/ApiResponses/Connection/SuccessfulResponse.json'
            ))
        ]);

        $connectionData = new ConnectionData(
            BaseProxy::TEST_MODE,
            'test',
            new AuthorizationCredentials('test_username', 'test_password')
        );

        $this->proxy->validateConnection(new ValidateConnectionRequest($connectionData));
        self::assertCount(1, $this->httpClient->getHistory());
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertEquals(HttpClient::HTTP_METHOD_GET, $lastRequest['method']);
    }

    /**
     * @return void
     *
     * @throws InvalidEnvironmentException
     */
    public function testConnectionRequestSuccessfulResponse(): void
    {
        $exception = null;
        $rawResponseBody = file_get_contents(
            __DIR__ . '/../../Common/ApiResponses/Connection/SuccessfulResponse.json'
        );

        $this->httpClient->setMockResponses([new HttpResponse(204, [], $rawResponseBody)]);
        $connectionData = new ConnectionData(
            BaseProxy::TEST_MODE,
            'test',
            new AuthorizationCredentials('test_username', 'test_password')
        );

        try {
            $this->proxy->validateConnection(new ValidateConnectionRequest($connectionData));
        } catch (Exception $exception) {
        }

        self::assertNull($exception);
    }

    /**
     * @return void
     *
     * @throws HttpRequestException
     * @throws InvalidEnvironmentException
     */
    public function testConnectionRequestUnauthorizedResponse(): void
    {
        $exception = null;
        $rawResponseBody = file_get_contents(__DIR__ . '/../../Common/ApiResponses/Connection/InvalidCredentialsResponse.json');
        $this->httpClient->setMockResponses([new HttpResponse(401, [], $rawResponseBody)]);

        $connectionData = new ConnectionData(
            BaseProxy::TEST_MODE,
            'test',
            new AuthorizationCredentials('test_username', 'test_password')
        );

        try {
            $this->proxy->validateConnection(new ValidateConnectionRequest($connectionData));
        } catch (HttpApiUnauthorizedException $exception) {
        }

        self::assertNotNull($exception);
        self::assertEquals(401, $exception->getCode());

        $responseBody = json_decode($rawResponseBody, true);
        $errors = $responseBody['errors'] ?? [];

        self::assertCount(1, $errors);
        self::assertEquals('Access denied', $errors[0]);
        self::assertEquals($responseBody['errors'] ?? [], $exception->getErrors());
    }
}
