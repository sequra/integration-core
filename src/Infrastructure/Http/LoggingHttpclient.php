<?php

namespace SeQura\Core\Infrastructure\Http;

use SeQura\Core\Infrastructure\Logger\LogContextData;
use SeQura\Core\Infrastructure\Logger\Logger;

class LoggingHttpclient extends HttpClient
{
    /**
     * Headers whose value authenticates the caller. They are logged without it: a log
     * is read by support and by the shop owner, and is served out of the shop on
     * request, while the value here is the credential the merchant connected with.
     */
    protected const SECRET_HEADERS = ['authorization', 'proxy-authorization', 'cookie', 'set-cookie'];

    /**
     * Stands in for the value of a header that carries a secret.
     */
    protected const SECRET_PLACEHOLDER = '***';

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
                new LogContextData('Headers', $this->maskedHeaders($headers)),
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
                new LogContextData('Headers', $this->maskedHeaders($response->getHeaders())),
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
                new LogContextData('Headers', $this->maskedHeaders($headers)),
                new LogContextData('Content', $body),
            )
        );

        $this->client->requestAsync($method, $url, $headers, $body);
    }

    /**
     * Returns the given headers as JSON, with the value of every header that carries a
     * secret replaced. The name is kept, so a log still shows which headers a request
     * was made with.
     *
     * @param mixed[]|null $headers
     *
     * @return string
     */
    protected function maskedHeaders(?array $headers): string
    {
        $masked = [];

        foreach ((array)$headers as $name => $value) {
            $masked[$name] = \in_array(strtolower((string)$name), self::SECRET_HEADERS, true)
                ? $this->maskedValue((string)$name, $value)
                : $value;
        }

        return (string)json_encode($masked);
    }

    /**
     * Returns the value of a header that carries a secret, as it may be logged. A
     * header of this library holds the whole "Name: value" line, so the name is kept
     * and only what follows it is dropped.
     *
     * @param string $name
     * @param mixed $value
     *
     * @return string
     */
    protected function maskedValue(string $name, $value): string
    {
        if (!\is_string($value)) {
            return self::SECRET_PLACEHOLDER;
        }

        $separator = strpos($value, ': ');

        if ($separator === false || strpos($value, $name) !== 0) {
            return self::SECRET_PLACEHOLDER;
        }

        return substr($value, 0, $separator + 2) . self::SECRET_PLACEHOLDER;
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
