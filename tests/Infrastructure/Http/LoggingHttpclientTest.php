<?php

namespace SeQura\Core\Tests\Infrastructure\Http;

use SeQura\Core\Infrastructure\Http\Exceptions\HttpCommunicationException;
use SeQura\Core\Infrastructure\Http\HttpResponse;
use SeQura\Core\Infrastructure\Http\LoggingHttpclient;
use SeQura\Core\Infrastructure\Logger\LogContextData;
use SeQura\Core\Infrastructure\Logger\LogData;
use SeQura\Core\Tests\Infrastructure\Common\BaseInfrastructureTestWithServices;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TestHttpClient;

/**
 * Class LoggingHttpclientTest.
 *
 * @package SeQura\Core\Tests\Infrastructure\Http
 */
class LoggingHttpclientTest extends BaseInfrastructureTestWithServices
{
    /**
     * @var LoggingHttpclient
     */
    private $client;

    /**
     * @var TestHttpClient
     */
    private $wrappedClient;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->wrappedClient = new TestHttpClient();
        $this->client = new LoggingHttpclient($this->wrappedClient);
    }

    /**
     * @return void
     *
     * @throws HttpCommunicationException
     */
    public function testItKeepsTheAuthorizationHeaderOutOfTheLog(): void
    {
        // Arrange
        $this->wrappedClient->setMockResponses([new HttpResponse(200, [], '{}')]);

        // Act
        $this->client->request(
            'POST',
            'https://sandbox.sequrapi.com/orders',
            [
                'Content-Type' => 'Content-Type: application/json',
                'Authorization' => 'Authorization: Basic bWVyY2hhbnQ6c2VjcmV0',
            ],
            '{}'
        );

        // Assert
        $headers = $this->loggedHeaders();

        self::assertNotContains('Authorization: Basic bWVyY2hhbnQ6c2VjcmV0', $headers);
        self::assertContains('{"Content-Type":"Content-Type: application\\/json",'
            . '"Authorization":"Authorization: ***"}', $headers);
    }

    /**
     * @return void
     *
     * @throws HttpCommunicationException
     */
    public function testItKeepsTheAuthorizationOfANumericallyKeyedHeaderListOutOfTheLog(): void
    {
        // Arrange
        $this->wrappedClient->setMockResponses([new HttpResponse(200, [], '{}')]);

        // Act
        $this->client->request(
            'POST',
            'https://sandbox.sequrapi.com/orders',
            [
                'Content-Type: application/json',
                'Authorization: Basic bWVyY2hhbnQ6c2VjcmV0',
            ],
            '{}'
        );

        // Assert
        $headers = implode("\n", $this->loggedHeaders());

        self::assertStringNotContainsString('bWVyY2hhbnQ6c2VjcmV0', $headers);
        self::assertStringContainsString('["Content-Type: application\\/json","Authorization: ***"]', $headers);
    }

    /**
     * @return void
     *
     * @throws HttpCommunicationException
     */
    public function testItKeepsACookieOutOfTheLog(): void
    {
        // Arrange
        $this->wrappedClient->setMockResponses([
            new HttpResponse(200, ['set-cookie' => 'session=secret'], '{}'),
        ]);

        // Act
        $this->client->request('GET', 'https://sandbox.sequrapi.com/orders', [], '');

        // Assert
        $headers = $this->loggedHeaders();

        self::assertNotContains('{"set-cookie":"session=secret"}', $headers);
        self::assertContains('{"set-cookie":"***"}', $headers);
    }

    /**
     * @return void
     */
    public function testItLogsAsyncRequestHeadersWithoutTheAuthorization(): void
    {
        // Act
        $this->client->requestAsync(
            'POST',
            'https://sandbox.sequrapi.com/orders',
            ['Authorization' => 'Authorization: Basic bWVyY2hhbnQ6c2VjcmV0'],
            '{}'
        );

        // Assert
        self::assertContains('{"Authorization":"Authorization: ***"}', $this->loggedHeaders());
    }

    /**
     * Returns the Headers context of every record logged so far.
     *
     * @return string[]
     */
    private function loggedHeaders(): array
    {
        $headers = [];

        /** @var LogData $record */
        foreach ($this->shopLogger->loggedMessages as $record) {
            /** @var LogContextData $context */
            foreach ($record->getContext() as $context) {
                if ($context->getName() === 'Headers') {
                    $headers[] = $context->getValue();
                }
            }
        }

        return $headers;
    }
}
