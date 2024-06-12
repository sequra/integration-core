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
     * @var array
     */
    protected $body;

    /**
     * @var array
     */
    protected $queries;

    /**
     * @var array
     */
    protected $headers;

    /**
     * HttpRequest constructor.
     *
     * @param string $endpoint
     * @param array $body
     * @param array $queries
     * @param array $headers
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
     * @return array
     */
    public function getBody(): array
    {
        return $this->body;
    }

    /**
     * @param array $body
     */
    public function setBody(array $body): void
    {
        $this->body = $body;
    }

    /**
     * @return array
     */
    public function getQueries(): array
    {
        return $this->queries;
    }

    /**
     * @param array $queries
     */
    public function setQueries(array $queries): void
    {
        $this->queries = $queries;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @param array $headers
     */
    public function setHeaders(array $headers): void
    {
        $this->headers = $headers;
    }
}
