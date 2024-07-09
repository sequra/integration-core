<?php

namespace SeQura\Core\Infrastructure\Http;

use SeQura\Core\Infrastructure\Configuration\Configuration;
use SeQura\Core\Infrastructure\Http\DTO\Options;
use SeQura\Core\Infrastructure\Http\Exceptions\HttpCommunicationException;
use SeQura\Core\Infrastructure\ServiceRegister;

/**
 * Class HttpClient.
 *
 * @package SeQura\Core\Infrastructure\Http
 */
abstract class HttpClient
{
    /**
     * Fully qualified name of this class.
     */
    public const CLASS_NAME = __CLASS__;
    /**
     * Unauthorized HTTP status code.
     */
    public const HTTP_STATUS_CODE_UNAUTHORIZED = 401;
    /**
     * Forbidden HTTP status code.
     */
    public const HTTP_STATUS_CODE_FORBIDDEN = 403;
    /**
     * Not found HTTP status code.
     */
    public const HTTP_STATUS_CODE_NOT_FOUND = 404;
    /**
     * Conflict HTTP status code.
     */
    public const HTTP_STATUS_CODE_CONFLICT = 409;
    /**
     * HTTP GET method.
     */
    public const HTTP_METHOD_GET = 'GET';
    /**
     * HTTP POST method.
     */
    public const HTTP_METHOD_POST = 'POST';
    /**
     * HTTP PUT method.
     */
    public const HTTP_METHOD_PUT = 'PUT';
    /**
     * HTTP DELETE method.
     */
    public const HTTP_METHOD_DELETE = 'DELETE';
    /**
     * HTTP PATCH method.
     */
    public const HTTP_METHOD_PATCH = 'PATCH';
    /**
     * Indicates if the instance is currently in the autoconfiguration mode.
     *
     * @var bool
     */
    protected $autoConfigurationMode = false;
    /**
     * Configuration service.
     *
     * @var Configuration
     */
    protected $configService;
    /**
     * An array of additional HTTP configuration options.
     *
     * @var array
     */
    protected $httpConfigurationOptions;

    /**
     * Create and send request.
     *
     * @param string $method HTTP method (GET, POST, PUT, DELETE etc.)
     * @param string $url Request URL. Full URL where request should be sent.
     * @param array|null $headers Request headers to send. Key as header name and value as header content. Optional.
     * @param string $body Request payload. String data to send as HTTP request payload. Optional.
     *
     * @return HttpResponse Response from making HTTP request.
     *
     * @throws HttpCommunicationException
     */
    public function request(string $method, string $url, ?array $headers = array(), string $body = ''): HttpResponse
    {
        return $this->sendHttpRequest($method, $url, $headers, $body);
    }

    /**
     * Create and send request asynchronously.
     *
     * @param string $method HTTP method (GET, POST, PUT, DELETE etc.)
     * @param string $url Request URL. Full URL where request should be sent.
     * @param array|null $headers Request headers to send. Key as header name and value as header content. Optional.
     * @param string $body Request payload. String data to send as HTTP request payload. Optional.
     */
    public function requestAsync(string $method, string $url, ?array $headers = array(), string $body = ''): void
    {
        $this->sendHttpRequestAsync($method, $url, $headers, $body);
    }

    /**
     * Autoconfigures http call options. Tries to make a request to the provided URL with all configured
     * configurations of HTTP options. When first succeeds, stored options should be used.
     *
     * @param string $method HTTP method (GET, POST, PUT, DELETE etc.)
     * @param string $url Request URL. Full URL where request should be sent.
     * @param array|null $headers Request headers to send. Key as header name and value as header content. Optional.
     * @param string $body Request payload. String data to send as HTTP request payload. Optional.
     *
     * @return bool TRUE if configuration went successfully; otherwise, FALSE.
     */
    public function autoConfigure(string $method, string $url, ?array $headers = array(), string $body = ''): bool
    {
        $this->autoConfigurationMode = true;
        if ($this->isRequestSuccessful($method, $url, $headers, $body)) {
            return true;
        }

        $domain = parse_url($url, PHP_URL_HOST);
        $combinations = $this->getAutoConfigurationOptionsCombinations($method, $url);
        foreach ($combinations as $combination) {
            $this->setAdditionalOptions($domain, $combination);
            if ($this->isRequestSuccessful($method, $url, $headers, $body)) {
                $this->autoConfigurationMode = false;

                return true;
            }

            // if request is not successful, reset options combination.
            $this->resetAdditionalOptions($domain);
        }

        $this->autoConfigurationMode = false;

        return false;
    }

    /**
     * Create and send request.
     *
     * @param string $method HTTP method (GET, POST, PUT, DELETE etc.)
     * @param string $url Request URL. Full URL where request should be sent.
     * @param array|null $headers Request headers to send. Key as header name and value as header content. Optional.
     * @param string $body Request payload. String data to send as HTTP request payload. Optional.
     *
     * @return HttpResponse Response object.
     *
     * @throws HttpCommunicationException
     *      Only in situation when there is no connection or no response.
     */
    abstract protected function sendHttpRequest(
        string $method,
        string $url,
        ?array $headers = array(),
        string $body = ''
    ): HttpResponse;

    /**
     * Create and send request asynchronously.
     *
     * @param string $method HTTP method (GET, POST, PUT, DELETE etc.)
     * @param string $url Request URL. Full URL where request should be sent.
     * @param array|null $headers Request headers to send. Key as header name and value as header content. Optional.
     * @param string $body Request payload. String data to send as HTTP request payload. Optional.  Default value for
     * request body is '1' to ensure minimal request data in case of POST, PUT, PATCH methods.
     *
     * @return void
     */
    abstract protected function sendHttpRequestAsync(
        string $method,
        string $url,
        ?array $headers = array(),
        string $body = '1'
    ): void;

    /**
     * Get additional options combinations for specified method and url.
     *
     * @param string $method HTTP method (GET, POST, PUT, DELETE etc.)
     * @param string $url Request URL.
     *
     * @return array
     *  Array of additional options combinations. Each array item should be an array of Options instances.
     *
     * @noinspection PhpUnusedParameterInspection
     */
    protected function getAutoConfigurationOptionsCombinations(string $method, string $url): array
    {
        // Left blank intentionally so specific implementations can override this method,
        // in order to return all possible combinations for additional HTTP options
        return array();
    }

    /**
     * Save additional options for request.
     *
     * @param string $domain A domain for which to set configuration options.
     * @param Options[] $options Additional options to add to HTTP request.
     */
    protected function setAdditionalOptions(string $domain, array $options): void
    {
        $this->httpConfigurationOptions = null;
        $this->getConfigService()->setHttpConfigurationOptions($domain, $options);
    }

    /**
     * Reset additional options for request to default value.
     *
     * @param string $domain A domain for which to reset configuration options.
     */
    protected function resetAdditionalOptions(string $domain): void
    {
        $this->httpConfigurationOptions = null;
        $this->getConfigService()->setHttpConfigurationOptions($domain, array());
    }

    /**
     * Gets HTTP options array from the configuration and transforms it to the key-value array.
     *
     * @param string $domain A domain for which to get configuration options.
     *
     * @return array A key-value array of HTTP configuration options.
     */
    protected function getAdditionalOptions(string $domain): array
    {
        if (!$this->httpConfigurationOptions) {
            $options = $this->getConfigService()->getHttpConfigurationOptions($domain);
            $this->httpConfigurationOptions = array();
            foreach ($options as $option) {
                $this->httpConfigurationOptions[$option->getName()] = $option->getValue();
            }
        }

        return $this->httpConfigurationOptions;
    }

    /**
     * Verifies the response and returns TRUE if valid, FALSE otherwise
     *
     * @param string $method HTTP method (GET, POST, PUT, DELETE etc.)
     * @param string $url Request URL. Full URL where request should be sent.
     * @param array|null $headers Request headers to send. Key as header name and value as header content. Optional.
     * @param string $body Request payload. String data to send as HTTP request payload. Optional.
     *
     * @return bool TRUE if request was successful; otherwise, FALSE.
     */
    protected function isRequestSuccessful(
        string $method,
        string $url,
        ?array $headers = array(),
        string $body = ''
    ): bool {
        try {
            $response = $this->request($method, $url, $headers, $body);
        } catch (HttpCommunicationException $ex) {
            $response = null;
        }

        return $response !== null && $response->isSuccessful();
    }

    /**
     * Gets the configuration service.
     *
     * @return Configuration Configuration service instance.
     */
    protected function getConfigService(): Configuration
    {
        if (empty($this->configService)) {
            $this->configService = ServiceRegister::getService(Configuration::CLASS_NAME);
        }

        return $this->configService;
    }
}
