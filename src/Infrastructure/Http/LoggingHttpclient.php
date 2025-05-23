<?php

namespace SeQura\Core\Infrastructure\Http;

use SeQura\Core\Infrastructure\Logger\LogContextData;
use SeQura\Core\Infrastructure\Logger\Logger;

class LoggingHttpclient extends HttpClient
{
    /**
     * @var HttpClient
     */
    protected $client;

    /**
     * LoggingHttpclient constructor.
     *
     * @param HttpClient $client
     */
    public function __construct(HttpClient $client)
    {
        $this->client = $client;
    }

    /**
     * @inheritdoc Create, log and send request.
     */
    public function request(string $method, string $url, ?array $headers = array(), string $body = ''): HttpResponse
    {
        Logger::logDebug(
            "Sending http request to $url",
            'Core',
            array(
                new LogContextData('Type', $method),
                new LogContextData('Endpoint', $url),
                new LogContextData('Headers', json_encode($headers)),
                new LogContextData('Content', $body),
            )
        );

        $response = $this->client->request($method, $url, $headers, $body);

        Logger::logDebug(
            "Http response from $url",
            'Core',
            array(
                new LogContextData('ResponseFor', "$method at $url"),
                new LogContextData('Status', $response->getStatus()),
                new LogContextData('Headers', json_encode($response->getHeaders())),
                new LogContextData('Content', $response->getBody()),
            )
        );

        return $response;
    }

    /**
     * @inheritdoc Create, log and send request asynchronously.
     */
    public function requestAsync(string $method, string $url, ?array $headers = array(), string $body = '1'): void
    {
        Logger::logDebug(
            "Sending async http request to $url",
            'Core',
            array(
                new LogContextData('Type', $method),
                new LogContextData('Endpoint', $url),
                new LogContextData('Headers', json_encode($headers)),
                new LogContextData('Content', $body),
            )
        );

        $this->client->requestAsync($method, $url, $headers, $body);
    }

    /**
     * @inheritdoc
     */
    protected function sendHttpRequest(
        string $method,
        string $url,
        ?array $headers = array(),
        string $body = ''
    ): HttpResponse {
        return $this->client->sendHttpRequest($method, $url, $headers, $body);
    }

    /**
     * @inheritdoc
     */
    protected function sendHttpRequestAsync(
        string $method,
        string $url,
        ?array $headers = array(),
        string $body = ''
    ): void {
        $this->client->sendHttpRequestAsync($method, $url, $headers, $body);
    }
}
