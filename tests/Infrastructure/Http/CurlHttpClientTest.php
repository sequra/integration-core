<?php

namespace SeQura\Core\Tests\Infrastructure\Http;

use SeQura\Core\Infrastructure\Http\CurlHttpClient;
use SeQura\Core\Infrastructure\Http\Exceptions\HttpCommunicationException;
use SeQura\Core\Infrastructure\Http\HttpClient;
use SeQura\Core\Infrastructure\Http\LoggingHttpclient;
use SeQura\Core\Tests\Infrastructure\Common\BaseInfrastructureTestWithServices;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TestCurlHttpClient;

class CurlHttpClientTest extends BaseInfrastructureTestWithServices
{
    /**
     * @var TestCurlHttpClient
     */
    private $httpClient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->httpClient = new TestCurlHttpClient();
        $this->httpClient->setMockResponses([['status' => 200, 'data' => '{}']]);
    }

    /**
     * @throws HttpCommunicationException
     */
    public function testSynchronousRequestBoundsTheConnectPhase(): void
    {
        // Act
        $this->httpClient->request(HttpClient::HTTP_METHOD_GET, 'https://sandbox.sequrapi.com/merchants');

        // Assert
        self::assertSame(CurlHttpClient::CONNECT_TIMEOUT, $this->httpClient->getCurlOptions()[CURLOPT_CONNECTTIMEOUT_MS]);
        self::assertSame(5000, CurlHttpClient::CONNECT_TIMEOUT);
    }

    /**
     * @throws HttpCommunicationException
     */
    public function testSynchronousRequestWithoutATimeoutKeepsTheDefaultTotalTimeout(): void
    {
        // Act
        $this->httpClient->request(HttpClient::HTTP_METHOD_GET, 'https://sandbox.sequrapi.com/merchants');

        // Assert
        self::assertSame(CurlHttpClient::DEFAULT_REQUEST_TIMEOUT, $this->httpClient->getCurlOptions()[CURLOPT_TIMEOUT_MS]);
    }

    /**
     * @throws HttpCommunicationException
     */
    public function testSynchronousRequestWithoutATimeoutKeepsTheConfiguredTotalTimeout(): void
    {
        // Arrange
        $this->shopConfig->setSyncRequestTimeout(60000);

        // Act
        $this->httpClient->request(HttpClient::HTTP_METHOD_GET, 'https://sandbox.sequrapi.com/merchants');

        // Assert
        self::assertSame(60000, $this->httpClient->getCurlOptions()[CURLOPT_TIMEOUT_MS]);
    }

    /**
     * @throws HttpCommunicationException
     */
    public function testSynchronousRequestUsesTheRequestTimeoutAsItsTotalTimeout(): void
    {
        // Act
        $this->httpClient->request(HttpClient::HTTP_METHOD_POST, 'https://sandbox.sequrapi.com/orders', [], '{}', 20000);

        // Assert
        self::assertSame(20000, $this->httpClient->getCurlOptions()[CURLOPT_TIMEOUT_MS]);
        self::assertSame(CurlHttpClient::CONNECT_TIMEOUT, $this->httpClient->getCurlOptions()[CURLOPT_CONNECTTIMEOUT_MS]);
    }

    /**
     * @throws HttpCommunicationException
     */
    public function testRequestTimeoutDoesNotRaiseAShorterConfiguredTotalTimeout(): void
    {
        // Arrange
        $this->shopConfig->setSyncRequestTimeout(10000);

        // Act
        $this->httpClient->request(HttpClient::HTTP_METHOD_POST, 'https://sandbox.sequrapi.com/orders', [], '{}', 20000);

        // Assert
        self::assertSame(10000, $this->httpClient->getCurlOptions()[CURLOPT_TIMEOUT_MS]);
    }

    /**
     * @throws HttpCommunicationException
     */
    public function testLoggingClientForwardsTheRequestTimeout(): void
    {
        // Arrange
        $loggingClient = new LoggingHttpclient($this->httpClient);

        // Act
        $loggingClient->request(HttpClient::HTTP_METHOD_POST, 'https://sandbox.sequrapi.com/orders', [], '{}', 20000);

        // Assert
        self::assertSame(20000, $this->httpClient->getCurlOptions()[CURLOPT_TIMEOUT_MS]);
    }
}
