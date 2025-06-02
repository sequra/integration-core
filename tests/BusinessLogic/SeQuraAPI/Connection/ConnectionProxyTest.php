<?php

namespace SeQura\Core\Tests\BusinessLogic\SeQuraAPI\Connection;

use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidEnvironmentException;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\AuthorizationCredentials;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\Credentials;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\CredentialsRequest;
use SeQura\Core\BusinessLogic\Domain\Connection\ProxyContracts\ConnectionProxyInterface;
use SeQura\Core\BusinessLogic\SeQuraAPI\BaseProxy;
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

        $this->proxy->getCredentials(new CredentialsRequest($connectionData));
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

        $this->proxy->getCredentials(new CredentialsRequest($connectionData));
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

        $this->proxy->getCredentials(new CredentialsRequest($connectionData));
        self::assertCount(1, $this->httpClient->getHistory());
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertEquals(HttpClient::HTTP_METHOD_GET, $lastRequest['method']);
    }

    /**
     * @return void
     *
     * @throws InvalidEnvironmentException
     * @throws HttpRequestException
     */
    public function testConnectionRequestSuccessfulResponse(): void
    {
        //Arrange
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

        $expectedResponse = [
            new Credentials('logeecom1', 'PT', 'EUR', 'assetsKey1', [
                "ref" => "logeecom1",
                "name" => null,
                "country" => "PT",
                "allowed_countries" => [
                    "ES"
                ],
                "currency" => "EUR",
                "assets_key" => "assetsKey1",
                "contract_options" => [],
                "extra_information" => [
                    "type" => "regular",
                    "phone_number" => ""
                ],
                "verify_signature" => false,
                "signature_secret" => "signature",
                "confirmation_path" => "default",
                "realm" => "svea"
            ]),
            new Credentials('logeecom2', 'FR', 'EUR', 'assetsKey2', [
                "ref" => "logeecom2",
                "name" => null,
                "country" => "FR",
                "allowed_countries" => [
                    "ES"
                ],
                "currency" => "EUR",
                "assets_key" => "assetsKey2",
                "contract_options" => [],
                "extra_information" => [
                    "type" => "regular",
                    "phone_number" => ""
                ],
                "verify_signature" => false,
                "signature_secret" => "f88ad25475df9310a9bf7d8a6e4fa7f9",
                "confirmation_path" => "default",
                "realm" => "svea"
            ]),
            new Credentials('logeecom3', 'IT', 'EUR', 'assetsKey3', [
                "ref" => "logeecom3",
                "name" => null,
                "country" => "IT",
                "allowed_countries" => [
                    "ES"
                ],
                "currency" => "EUR",
                "assets_key" => "assetsKey3",
                "contract_options" => [],
                "extra_information" => [
                    "type" => "regular",
                    "phone_number" => ""
                ],
                "verify_signature" => false,
                "signature_secret" => "d10263781ab8011e0797ea82d34cd3ad",
                "confirmation_path" => "default",
                "realm" => "svea"
            ]),
            new Credentials('logeecom4', 'ES', 'EUR', 'assetsKey4', [
                "ref" => "logeecom4",
                "name" => null,
                "country" => "ES",
                "allowed_countries" => [
                    "ES"
                ],
                "currency" => "EUR",
                "assets_key" => "assetsKey4",
                "contract_options" => [],
                "extra_information" => [
                    "type" => "regular",
                    "phone_number" => ""
                ],
                "verify_signature" => false,
                "signature_secret" => "6f3f3baae89ec0b324f346d73c6c8e70",
                "confirmation_path" => "default",
                "realm" => "sequra"
            ])
        ];

        //Act
        $response = $this->proxy->getCredentials(new CredentialsRequest($connectionData));

        //Assert
        self::assertNull($exception);
        self::assertEquals($expectedResponse, $response);
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
            $this->proxy->getCredentials(new CredentialsRequest($connectionData));
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
