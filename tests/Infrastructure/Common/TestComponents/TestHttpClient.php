<?php

/** @noinspection PhpUnused */

/** @noinspection PhpMissingDocCommentInspection */

namespace SeQura\Core\Tests\Infrastructure\Common\TestComponents;

use SeQura\Core\Infrastructure\Http\DTO\Options;
use SeQura\Core\Infrastructure\Http\Exceptions\HttpCommunicationException;
use SeQura\Core\Infrastructure\Http\HttpClient;
use SeQura\Core\Infrastructure\Http\HttpResponse;

class TestHttpClient extends HttpClient
{
    public const REQUEST_TYPE_SYNCHRONOUS = 1;
    public const REQUEST_TYPE_ASYNCHRONOUS = 2;

    public $calledAsync = false;
    public $additionalOptions;
    public $setAdditionalOptionsCallHistory = array();
    /**
     * @var array
     */
    private $responses;
    /**
     * @var array
     */
    private $history;
    /**
     * @var array
     */
    private $autoConfigurationCombinations = array();

    /**
     * @inheritdoc
     */
    public function request(string $method, string $url, ?array $headers = array(), string $body = ''): HttpResponse
    {
        return $this->sendHttpRequest($method, $url, $headers, $body);
    }

    /**
     * @inheritdoc
     */
    public function requestAsync(string $method, string $url, ?array $headers = array(), string $body = ''): void
    {
        $this->sendHttpRequestAsync($method, $url, $headers, $body);
    }

    /**
     * @inheritdoc
     */
    public function sendHttpRequest(
        string $method,
        string $url,
        ?array $headers = array(),
        string $body = ''
    ): HttpResponse {
        $this->history[] = array(
            'type' => self::REQUEST_TYPE_SYNCHRONOUS,
            'method' => $method,
            'url' => $url,
            'headers' => $headers,
            'body' => $body,
        );

        if (empty($this->responses)) {
            throw new HttpCommunicationException('No response');
        }

        return array_shift($this->responses);
    }

    /**
     * @inheritdoc
     */
    public function sendHttpRequestAsync(
        string $method,
        string $url,
        ?array $headers = array(),
        string $body = ''
    ): void {
        $this->calledAsync = true;

        $this->history[] = array(
            'type' => self::REQUEST_TYPE_ASYNCHRONOUS,
            'method' => $method,
            'url' => $url,
            'headers' => $headers,
            'body' => $body,
        );
    }

    /**
     * @inheritdoc
     */
    protected function getAutoConfigurationOptionsCombinations(string $method, string $url): array
    {
        if (empty($this->autoConfigurationCombinations)) {
            $this->setAdditionalOptionsCombinations(
                array(
                    array(new Options(CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4)),
                    array(new Options(CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V6)),
                )
            );
        }

        return $this->autoConfigurationCombinations;
    }

    /**
     * Sets the additional HTTP options combinations.
     *
     * @param array $combinations
     *
     * @return void
     */
    protected function setAdditionalOptionsCombinations(array $combinations): void
    {
        $this->autoConfigurationCombinations = $combinations;
    }

    /**
     * Save additional options for request.
     *
     * @param string|null $domain A domain for which to reset configuration options.
     * @param Options[] $options Additional option to add to HTTP request.
     *
     * @return void
     */
    protected function setAdditionalOptions(?string $domain, array $options): void
    {
        $this->setAdditionalOptionsCallHistory[] = $options;
        $this->additionalOptions = $options;
    }

    /**
     * Reset additional options for request to default value.
     *
     * @param string|null $domain A domain for which to reset configuration options.
     *
     * @return void
     */
    protected function resetAdditionalOptions(?string $domain): void
    {
        $this->additionalOptions = array();
    }

    /**
     * Set all mock responses.
     *
     * @param array $responses
     *
     * @return void
     */
    public function setMockResponses(array $responses): void
    {
        $this->responses = $responses;
    }

    /**
     * Return last request.
     *
     * @return array
     */
    public function getLastRequest(): array
    {
        return end($this->history);
    }

    /**
     * Return call history.
     *
     * @return array
     */
    public function getHistory(): array
    {
        return $this->history;
    }

    /**
     * Resets the history call stack.
     */
    public function resetHistory(): void
    {
        $this->history = null;
    }

    /**
     * Return last request.
     *
     * @return array
     */
    public function getLastRequestHeaders(): array
    {
        $lastRequest = $this->getLastRequest();

        return $lastRequest['headers'];
    }
}
