<?php

namespace SeQura\Core\BusinessLogic\SeQuraAPI;

/**
 * Class HttpRequest
 *
 * @package SeQura\Core\BusinessLogic\SeQuraAPI
 */
class HttpRequest
{
    /**
     * @var string
     */
    protected $endpoint;

    /**
     * @var mixed[]
     */
    protected $body;

    /**
     * @var mixed[]
     */
    protected $queries;

    /**
     * @var array<string,string>
     */
    protected $headers;

    /**
     * HttpRequest constructor.
     *
     * @param string $endpoint
     * @param mixed[] $body
     * @param mixed[] $queries
     * @param array<string,string> $headers
     */
    public function __construct(
        string $endpoint,
        array $body = [],
        array $queries = [],
        array $headers = []
    ) {
        $this->endpoint = $endpoint;
        $this->body = $body;
        $this->queries = $queries;
        $this->headers = $headers;
    }

    /**
     * @return string
     */
    public function getEndpoint(): string
    {
        return $this->endpoint;
    }

    /**
     * @param string $endpoint
     */
    public function setEndpoint(string $endpoint): void
    {
        $this->endpoint = $endpoint;
    }

    /**
     * @return mixed[]
     */
    public function getBody(): array
    {
        return $this->body;
    }

    /**
     * @param mixed[] $body
     */
    public function setBody(array $body): void
    {
        $this->body = $body;
    }

    /**
     * @return mixed[]
     */
    public function getQueries(): array
    {
        return $this->queries;
    }

    /**
     * @param mixed[] $queries
     */
    public function setQueries(array $queries): void
    {
        $this->queries = $queries;
    }

    /**
     * @return array<string,string>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @param array<string,string> $headers
     */
    public function setHeaders(array $headers): void
    {
        $this->headers = $headers;
    }
}
