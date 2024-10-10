<?php

namespace SeQura\Core\Infrastructure\Http;

/**
 * Class HttpResponse.
 *
 * @package SeQura\Core\Infrastructure\Http
 */
class HttpResponse
{
    /**
     * Fully qualified name of this class.
     */
    const CLASS_NAME = __CLASS__;
    /**
     * HTTP status.
     *
     * @var int
     */
    protected $status;
    /**
     * Response body.
     *
     * @var string
     */
    protected $body;
    /**
     * HTTP headers.
     *
     * @var array<string,string>
     */
    protected $headers;

    /**
     * HttpResponse constructor.
     *
     * @param int $status HTTP status
     * @param array<string,string> $headers HTTPS headers
     * @param string $body Response body
     */
    public function __construct($status, $headers, $body)
    {
        $this->status = $status;
        $this->headers = $headers;
        $this->body = $body;
    }

    /**
     * Returns response status.
     *
     * @return int HTTPS status.
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Returns response body.
     *
     * @return string Response body.
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Returns json decoded response body.
     *
     * @return mixed[] Response body decoded as json decode.
     */
    public function decodeBodyToArray(): array
    {
        $result = json_decode($this->body, true);

        return !empty($result) ? $result : array();
    }

    /**
     * Return. response headers.
     *
     * @return array<string,string> Array of HTTP headers.
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Verifies HTTP status code.
     *
     * @return bool Returns TRUE if in success range [200, 300); otherwise, FALSE.
     */
    public function isSuccessful()
    {
        return $this->status !== null && $this->getStatus() >= 200 && $this->getStatus() < 300;
    }
}
