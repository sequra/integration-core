<?php

namespace SeQura\Core\BusinessLogic\SeQuraAPI;

use Exception;
use SeQura\Core\BusinessLogic\SeQuraAPI\Exceptions\HttpApiInvalidRequestBodyException;
use SeQura\Core\BusinessLogic\SeQuraAPI\Exceptions\HttpApiInvalidUrlParameterException;
use SeQura\Core\BusinessLogic\SeQuraAPI\Exceptions\HttpApiNotFoundException;
use SeQura\Core\BusinessLogic\SeQuraAPI\Exceptions\HttpApiRequestException;
use SeQura\Core\BusinessLogic\SeQuraAPI\Exceptions\HttpApiUnauthorizedException;
use SeQura\Core\Infrastructure\Http\Exceptions\HttpRequestException;
use SeQura\Core\Infrastructure\Http\HttpClient;
use SeQura\Core\Infrastructure\Http\HttpResponse;

/**
 * Class BaseProxy
 *
 * @package SeQura\Core\BusinessLogic\SeQuraAPI
 */
abstract class BaseProxy
{
    /**
     * Base SeQura API URL.
     */
    protected const BASE_API_URL = 'sequrapi.com';

    /**
     * Test mode string.
     */
    public const TEST_MODE = 'sandbox';

    /**
     * Live mode string.
     */
    public const LIVE_MODE = 'live';

    /**
     * Http client instance.
     *
     * @var HttpClient
     */
    protected $httpClient;

    /**
     * @var string
     */
    protected $mode;

    /**
     * Proxy constructor.
     *
     * @param HttpClient $httpClient
     * @param string $mode
     */
    public function __construct(HttpClient $httpClient, string $mode = self::TEST_MODE)
    {
        $this->httpClient = $httpClient;
        $this->mode = $mode === self::LIVE_MODE ? self::LIVE_MODE : self::TEST_MODE;
    }

    /**
     * Performs GET HTTP request.
     *
     * @param HttpRequest $request
     *
     * @return HttpResponse Get HTTP response.
     *
     * @throws HttpRequestException
     */
    protected function get(HttpRequest $request): HttpResponse
    {
        return $this->call(HttpClient::HTTP_METHOD_GET, $request);
    }

    /**
     * Performs DELETE HTTP request.
     *
     * @param HttpRequest $request
     *
     * @return HttpResponse DELETE HTTP response.
     *
     * @throws HttpRequestException
     */
    protected function delete(HttpRequest $request): HttpResponse
    {
        return $this->call(HttpClient::HTTP_METHOD_DELETE, $request);
    }

    /**
     * Performs POST HTTP request.
     *
     * @param HttpRequest $request
     *
     * @return HttpResponse Response instance.
     *
     * @throws HttpRequestException
     */
    protected function post(HttpRequest $request): HttpResponse
    {
        return $this->call(HttpClient::HTTP_METHOD_POST, $request);
    }

    /**
     * Performs PUT HTTP request.
     *
     * @param HttpRequest $request
     *
     * @return HttpResponse Response instance.
     *
     * @throws HttpRequestException
     */
    protected function put(HttpRequest $request): HttpResponse
    {
        return $this->call(HttpClient::HTTP_METHOD_PUT, $request);
    }

    /**
     * Performs PATCH HTTP request.
     *
     * @param HttpRequest $request
     *
     * @return HttpResponse Response instance.
     *
     * @throws HttpRequestException
     */
    protected function patch(HttpRequest $request): HttpResponse
    {
        return $this->call(HttpClient::HTTP_METHOD_PATCH, $request);
    }

    /**
     * Performs HTTP call.
     *
     * @param string $method Specifies which http method is utilized in call.
     * @param HttpRequest $request
     *
     * @return HttpResponse Response instance.
     *
     * @throws HttpRequestException
     * @throws Exception
     */
    protected function call(string $method, HttpRequest $request): HttpResponse
    {
        $request->setHeaders(array_merge($this->getHeaders(), $request->getHeaders()));

        $url = $this->getRequestUrl($request);

        $response = $this->httpClient->request(
            $method,
            $url,
            $request->getHeaders(),
            $this->getEncodedBody($request)
        );

        $this->validateResponse($response);

        return $response;
    }

    /**
     * Retrieves default request headers.
     *
     * @return array Complete list of default request headers.
     */
    protected function getHeaders(): array
    {
        return [
            'Content-Type' => 'Content-Type: application/json',
            'Accept' => 'Accept: application/json',
        ];
    }

    /**
     * Encodes the request body into a JSON string.
     *
     * @param HttpRequest $request
     *
     * @return string
     */
    protected function getEncodedBody(HttpRequest $request): string
    {
        return (string)json_encode($request->getBody());
    }

    /**
     * Retrieves full request url.
     *
     * @param HttpRequest $request
     *
     * @return string Full request url.
     */
    protected function getRequestUrl(HttpRequest $request): string
    {
        $baseUrl = sprintf('https://%s.%s', $this->mode, trim(static::BASE_API_URL, '/'));
        $sanitizedEndpoint = ltrim($request->getEndpoint(), '/');
        $queryString = $this->getQueryString($request);

        return sprintf('%s/%s%s', $baseUrl, $sanitizedEndpoint, !empty($queryString) ? "?$queryString" : '');
    }

    /**
     * Prepares request's queries.
     *
     * @param HttpRequest $request
     *
     * @return string
     */
    protected function getQueryString(HttpRequest $request): string
    {
        return http_build_query($request->getQueries());
    }

    /**
     * Validates HTTP response.
     *
     * @param HttpResponse $response Response object to be validated.
     *
     * @throws HttpRequestException
     */
    protected function validateResponse(HttpResponse $response): void
    {
        if ($response->isSuccessful()) {
            return;
        }

        switch ($response->getStatus()) {
            case HttpClient::HTTP_STATUS_CODE_UNAUTHORIZED:
                throw HttpApiUnauthorizedException::fromErrorResponse($response, 'Wrong credentials.');
            case HttpClient::HTTP_STATUS_CODE_FORBIDDEN:
                throw HttpApiInvalidUrlParameterException::fromErrorResponse($response, 'Access forbidden.');
            case HttpClient::HTTP_STATUS_CODE_NOT_FOUND:
                throw HttpApiNotFoundException::fromErrorResponse($response, 'Page not found.');
            case HttpClient::HTTP_STATUS_CODE_CONFLICT:
                throw HttpApiInvalidRequestBodyException::fromErrorResponse($response, 'Invalid request body.');
            default:
                throw HttpApiRequestException::fromErrorResponse($response);
        }
    }
}
