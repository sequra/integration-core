<?php

namespace SeQura\Core\Tests\BusinessLogic\SeQuraAPI;

use SeQura\Core\BusinessLogic\SeQuraAPI\Exceptions\HttpApiRequestException;
use SeQura\Core\BusinessLogic\SeQuraAPI\HttpRequest;
use SeQura\Core\Infrastructure\Http\Exceptions\HttpRequestException;
use SeQura\Core\Infrastructure\Http\HttpClient;
use SeQura\Core\Infrastructure\Http\HttpResponse;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\BusinessLogic\SeQuraAPI\MockComponents\MockBaseProxy;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TestHttpClient;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class BaseProxyTest
 *
 * @package SeQura\Core\Tests\BusinessLogic\SeQuraAPI
 */
class BaseProxyTest extends BaseTestCase
{
    /**
     * @var TestHttpClient
     */
    protected $httpClient;
    /**
     * @var MockBaseProxy
     */
    protected $proxy;

    /**
     * @return void
     *
     * @throws RepositoryClassException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->httpClient = TestServiceRegister::getService(HttpClient::class);
        $this->proxy = new MockBaseProxy($this->httpClient);
    }

    /**
     * @return void
     *
     * @throws HttpRequestException
     */
    public function testGetMethod(): void
    {
        $this->prepareSuccessfulResponse();

        $response = $this->proxy->get(new HttpRequest('/hello'));
        self::assertNotNull($response);
        self::assertNotEmpty($this->httpClient->getHistory());

        $request = $this->httpClient->getLastRequest();
        self::assertEquals(HttpClient::HTTP_METHOD_GET, $request['method']);
        self::assertEquals('[]', (string)$request['body']);
        self::assertEquals('https://sandbox.test-sequra-proxy-url.domain.com/test-path/hello', $request['url']);
    }

    /**
     * @return void
     *
     * @throws HttpRequestException
     */
    public function testDeleteMethod(): void
    {
        $this->prepareSuccessfulResponse();

        $response = $this->proxy->delete(new HttpRequest('/hello'));
        self::assertNotNull($response);
        self::assertNotEmpty($this->httpClient->getHistory());

        $request = $this->httpClient->getLastRequest();
        self::assertEquals(HttpClient::HTTP_METHOD_DELETE, $request['method']);
        self::assertEquals('[]', $request['body']);
        self::assertEquals('https://sandbox.test-sequra-proxy-url.domain.com/test-path/hello', $request['url']);
    }

    /**
     * @return void
     *
     * @throws HttpRequestException
     */
    public function testPostMethod(): void
    {
        $this->prepareSuccessfulResponse();
        $request = new HttpRequest('/hello');
        $body = ['test' => 123];
        $request->setBody($body);

        $response = $this->proxy->post($request);
        self::assertNotNull($response);
        self::assertNotEmpty($this->httpClient->getHistory());

        $request = $this->httpClient->getLastRequest();
        self::assertEquals(HttpClient::HTTP_METHOD_POST, $request['method']);
        self::assertEquals(json_encode($body), $request['body']);
        self::assertEquals('https://sandbox.test-sequra-proxy-url.domain.com/test-path/hello', $request['url']);
    }

    /**
     * @return void
     *
     * @throws HttpRequestException
     */
    public function testPutMethod(): void
    {
        $this->prepareSuccessfulResponse();
        $body = array('test' => 123);
        $request = new HttpRequest('/hello');
        $request->setBody($body);

        $response = $this->proxy->put($request);
        self::assertNotNull($response);
        self::assertNotEmpty($this->httpClient->getHistory());

        $request = $this->httpClient->getLastRequest();
        self::assertEquals(HttpClient::HTTP_METHOD_PUT, $request['method']);
        self::assertEquals(json_encode($body), $request['body']);
        self::assertEquals('https://sandbox.test-sequra-proxy-url.domain.com/test-path/hello', $request['url']);
    }

    /**
     * @return void
     *
     * @throws HttpRequestException
     */
    public function testPatchMethod(): void
    {
        $this->prepareSuccessfulResponse();
        $body = array('test' => 123);
        $request = new HttpRequest('/hello');
        $request->setBody($body);

        $response = $this->proxy->patch($request);
        self::assertNotNull($response);
        self::assertNotEmpty($this->httpClient->getHistory());

        $request = $this->httpClient->getLastRequest();
        self::assertEquals(HttpClient::HTTP_METHOD_PATCH, $request['method']);
        self::assertEquals(json_encode($body), $request['body']);
        self::assertEquals('https://sandbox.test-sequra-proxy-url.domain.com/test-path/hello', $request['url']);
    }

    /**
     * @return void
     *
     * @throws HttpRequestException
     */
    public function testPostMethodWithEmptyBody(): void
    {
        $this->prepareSuccessfulResponse();

        $response = $this->proxy->post(new HttpRequest('/hello'));
        self::assertNotNull($response);
        self::assertNotEmpty($this->httpClient->getHistory());

        $request = $this->httpClient->getLastRequest();
        self::assertEquals(HttpClient::HTTP_METHOD_POST, $request['method']);
        self::assertEquals('[]', $request['body']);
        self::assertEquals('https://sandbox.test-sequra-proxy-url.domain.com/test-path/hello', $request['url']);
    }

    /**
     * @return void
     *
     * @throws HttpRequestException
     */
    public function testDefaultRequestHeaders(): void
    {
        $this->prepareSuccessfulResponse();
        $request = new HttpRequest('/hello');

        $response = $this->proxy->post($request);
        self::assertNotNull($response);
        self::assertNotEmpty($this->httpClient->getHistory());

        $headers = $this->httpClient->getLastRequestHeaders();
        self::assertNotEmpty($headers);
        self::assertArrayHasKey('Content-Type', $headers);
        self::assertEquals('Content-Type: application/json', $headers['Content-Type']);
        self::assertArrayHasKey('Accept', $headers);
        self::assertEquals('Accept: application/json', $headers['Accept']);
    }

    /**
     * @return void
     *
     * @throws HttpRequestException
     */
    public function testAdditionalHeaders(): void
    {
        $this->prepareSuccessfulResponse();
        $headers = array('test' => 123);
        $request = new HttpRequest('/hello');
        $request->setHeaders($headers);

        $response = $this->proxy->get($request);
        self::assertNotNull($response);
        self::assertNotEmpty($this->httpClient->getHistory());

        $request = $this->httpClient->getLastRequest();
        self::assertEquals(HttpClient::HTTP_METHOD_GET, $request['method']);
        self::assertEquals('[]', $request['body']);
        self::assertEquals('https://sandbox.test-sequra-proxy-url.domain.com/test-path/hello', $request['url']);
        $headers = $request['headers'];
        self::assertNotEmpty($headers);
        self::assertArrayHasKey('test', $headers);
        self::assertEquals(123, $headers['test']);
    }

    /**
     * @return void
     *
     * @throws HttpRequestException
     */
    public function testAdditionalQueryParams(): void
    {
        $this->prepareSuccessfulResponse();
        $request = new HttpRequest('/hello');
        $request->setQueries(['propertyName' => 'property Value']);

        $response = $this->proxy->get($request);
        self::assertNotNull($response);
        self::assertNotEmpty($this->httpClient->getHistory());

        $request = $this->httpClient->getLastRequest();
        self::assertEquals(HttpClient::HTTP_METHOD_GET, $request['method']);
        self::assertEquals('[]', $request['body']);
        self::assertStringEndsWith('?propertyName=property+Value', $request['url']);
    }

    /**
     * @dataProvider baseUrlProvider
     *
     * @param string $baseUrl
     *
     * @return void
     *
     * @throws HttpRequestException
     */
    public function testSecureUrlIsEnforced(string $baseUrl): void
    {
        $this->prepareSuccessfulResponse();

        $request = new HttpRequest('/hello');
        $proxy = new MockBaseProxy($this->httpClient, $baseUrl);

        $response = $proxy->get($request);
        self::assertNotNull($response);
        self::assertNotEmpty($this->httpClient->getHistory());

        $request = $this->httpClient->getLastRequest();
        self::assertEquals(HttpClient::HTTP_METHOD_GET, $request['method']);
        self::assertEquals('[]', (string)$request['body']);
        self::assertEquals('https://sandbox.test-sequra-proxy-url.domain.com/test-path/hello', $request['url']);
    }

    /**
     * @return array[]
     */
    public function baseUrlProvider(): array
    {
        return [
            ['test-sequra-proxy-url.domain.com/test-path/'],
            ['http://test-sequra-proxy-url.domain.com/test-path/'],
            ['https://test-sequra-proxy-url.domain.com/test-path'],
        ];
    }

    /**
     * @return void
     *
     * @throws HttpRequestException
     */
    public function testFailedResponse(): void
    {
        $exception = null;
        $request = new HttpRequest('hello');
        $expectedResponse = $this->getFailResponse();
        $expectedResponseBody = json_decode($expectedResponse->getBody(), true);
        $this->httpClient->setMockResponses([$expectedResponse]);

        try {
            $this->proxy->get($request);
        } catch (HttpApiRequestException $exception) {
        }

        self::assertNotNull($exception);
        self::assertEquals(400, $exception->getCode());
        self::assertEquals($expectedResponseBody['errors'], $exception->getErrors());
    }

    /**
     * Sets a successful mock response
     *
     * @return void
     */
    protected function prepareSuccessfulResponse(): void
    {
        $this->httpClient->setMockResponses([new HttpResponse(200, [], '')]);
    }

    /**
     * Creates an error response
     *
     * @return HttpResponse
     */
    protected function getFailResponse(): HttpResponse
    {
        return new HttpResponse(400, [], json_encode(['errors' => ['some error string', 'second error string']]));
    }
}
