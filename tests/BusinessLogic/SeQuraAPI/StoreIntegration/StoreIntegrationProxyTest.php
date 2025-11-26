<?php

namespace SeQura\Core\Tests\BusinessLogic\SeQuraAPI\StoreIntegration;

use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidEnvironmentException;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\AuthorizationCredentials;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use SeQura\Core\BusinessLogic\Domain\Connection\RepositoryContracts\ConnectionDataRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Exceptions\CapabilitiesEmptyException;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Exceptions\InvalidWebhookUrlException;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models\Capability;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models\CreateStoreIntegrationRequest;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models\DeleteStoreIntegrationRequest;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models\DeleteStoreIntegrationResponse;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\ProxyContracts\StoreIntegrationsProxyInterface;
use SeQura\Core\BusinessLogic\SeQuraAPI\BaseProxy;
use SeQura\Core\Infrastructure\Http\HttpClient;
use SeQura\Core\Infrastructure\Http\HttpResponse;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TestHttpClient;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class StoreIntegrationProxyTest.
 *
 * @package SeQuraAPI\StoreIntegration
 */
class StoreIntegrationProxyTest extends BaseTestCase
{
    /**
     * @var StoreIntegrationsProxyInterface
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

        $this->proxy = TestServiceRegister::getService(StoreIntegrationsProxyInterface::class);
        $repository = TestServiceRegister::getService(ConnectionDataRepositoryInterface::class);

        $connectionData = new ConnectionData(
            BaseProxy::TEST_MODE,
            'test',
            'sequra',
            new AuthorizationCredentials('test_username', 'test_password')
        );

        $repository->setConnectionData($connectionData);
    }

    /**
     * @return void
     *
     * @throws CapabilitiesEmptyException
     * @throws InvalidWebhookUrlException
     */
    public function testCreateStoreIntegrationRequestUrl(): void
    {
        // arrange
        $this->httpClient->setMockResponses([
            new HttpResponse(204, [
                'location' => 'https://sandbox.sequrapi.com/store_integrations/4'
            ], file_get_contents(
                __DIR__ . '/../../Common/ApiResponses/StoreIntegration/CreateStoreIntegrationResponse.json'
            ))
        ]);

        $request = new CreateStoreIntegrationRequest('merchant1', 'https://test.com', [Capability::general()]);
        // act
        $this->proxy->createStoreIntegration($request);

        // assert
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertCount(1, $this->httpClient->getHistory());
        self::assertEquals('https://sandbox.sequrapi.com/store_integrations', $lastRequest['url']);
    }

    /**
     * @return void
     *
     * @throws CapabilitiesEmptyException
     * @throws InvalidWebhookUrlException
     */
    public function testCreateStoreIntegrationAuthHeader(): void
    {
        // arrange
        $this->httpClient->setMockResponses([
            new HttpResponse(204, [
                'location' => 'https://sandbox.sequrapi.com/store_integrations/4'
            ], file_get_contents(
                __DIR__ . '/../../Common/ApiResponses/StoreIntegration/CreateStoreIntegrationResponse.json'
            ))
        ]);

        $request = new CreateStoreIntegrationRequest('merchant1', 'https://test.com', [Capability::general()]);
        // act
        $this->proxy->createStoreIntegration($request);

        // assert
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertCount(1, $this->httpClient->getHistory());
        self::assertArrayHasKey('Authorization', $lastRequest['headers']);
    }

    /**
     * @return void
     *
     * @throws CapabilitiesEmptyException
     * @throws InvalidWebhookUrlException
     */
    public function testCreateStoreIntegrationMethod(): void
    {
        // arrange
        $this->httpClient->setMockResponses([
            new HttpResponse(204, [
                'location' => 'https://sandbox.sequrapi.com/store_integrations/4'
            ], file_get_contents(
                __DIR__ . '/../../Common/ApiResponses/StoreIntegration/CreateStoreIntegrationResponse.json'
            ))
        ]);

        $request = new CreateStoreIntegrationRequest('merchant1', 'https://test.com', [Capability::general()]);
        // act
        $this->proxy->createStoreIntegration($request);

        // assert
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertCount(1, $this->httpClient->getHistory());
        self::assertEquals(HttpClient::HTTP_METHOD_POST, $lastRequest['method']);
    }

    /**
     * @return void
     *
     * @throws CapabilitiesEmptyException
     * @throws InvalidWebhookUrlException
     */
    public function testCreateStoreIntegrationResponse(): void
    {
        // arrange
        $this->httpClient->setMockResponses([
            new HttpResponse(204, [
                'location' => 'https://sandbox.sequrapi.com/store_integrations/4'
            ], file_get_contents(
                __DIR__ . '/../../Common/ApiResponses/StoreIntegration/CreateStoreIntegrationResponse.json'
            ))
        ]);

        $request = new CreateStoreIntegrationRequest('merchant1', 'https://test.com', [Capability::general()]);
        // act
        $response = $this->proxy->createStoreIntegration($request);

        // assert
        self::assertEquals('4', $response->getIntegrationId());
    }

    /**
     * @return void
     */
    public function testDeleteStoreIntegrationRequestUrl(): void
    {
        // arrange
        $this->httpClient->setMockResponses([
            new HttpResponse(204, [], file_get_contents(
                __DIR__ . '/../../Common/ApiResponses/StoreIntegration/DeleteStoreIntegrationResponse.json'
            ))
        ]);

        $request = new DeleteStoreIntegrationRequest('merchant1', '4');
        // act
        $this->proxy->deleteStoreIntegration($request);

        // assert
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertCount(1, $this->httpClient->getHistory());
        self::assertEquals('https://sandbox.sequrapi.com/store_integrations/4', $lastRequest['url']);
    }

    /**
     * @return void
     */
    public function testDeleteStoreIntegrationAuthHeader(): void
    {
        // arrange
        $this->httpClient->setMockResponses([
            new HttpResponse(204, [], file_get_contents(
                __DIR__ . '/../../Common/ApiResponses/StoreIntegration/DeleteStoreIntegrationResponse.json'
            ))
        ]);

        $request = new DeleteStoreIntegrationRequest('merchant1', '4');
        // act
        $this->proxy->deleteStoreIntegration($request);

        // assert
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertCount(1, $this->httpClient->getHistory());
        self::assertArrayHasKey('Authorization', $lastRequest['headers']);
    }

    /**
     * @return void
     */
    public function testDeleteStoreIntegrationMethod(): void
    {
        // arrange
        $this->httpClient->setMockResponses([
            new HttpResponse(204, [], file_get_contents(
                __DIR__ . '/../../Common/ApiResponses/StoreIntegration/DeleteStoreIntegrationResponse.json'
            ))
        ]);

        $request = new DeleteStoreIntegrationRequest('merchant1', '4');
        // act
        $this->proxy->deleteStoreIntegration($request);

        // assert
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertCount(1, $this->httpClient->getHistory());
        self::assertEquals(HttpClient::HTTP_METHOD_DELETE, $lastRequest['method']);
    }

    /**
     * @return void
     */
    public function testDeleteStoreIntegrationResponse(): void
    {
        // arrange
        $this->httpClient->setMockResponses([
            new HttpResponse(204, [], file_get_contents(
                __DIR__ . '/../../Common/ApiResponses/StoreIntegration/DeleteStoreIntegrationResponse.json'
            ))
        ]);

        $request = new DeleteStoreIntegrationRequest('merchant1', '4');
        // act
        $response = $this->proxy->deleteStoreIntegration($request);

        // assert
        self::assertEquals(new DeleteStoreIntegrationResponse(), $response);
    }
}
