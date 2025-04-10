<?php

namespace SeQura\Core\BusinessLogic\SeQuraAPI\Exceptions;

use SeQura\Core\Infrastructure\Http\Exceptions\HttpRequestException;
use SeQura\Core\Infrastructure\Http\HttpResponse;

/**
 * Class HttpApiRequestException
 *
 * @package SeQura\Core\BusinessLogic\SeQuraAPI\Exceptions
 */
/**
 * @phpstan-consistent-constructor
 */
class HttpApiRequestException extends HttpRequestException
{
    /**
     * @var string[]
     */
    protected $errors = [];

    public function __construct(string $message = '', int $code = 0, HttpRequestException $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Creates an instance of HttpApiRequestException.
     *
     * @param HttpResponse $response
     * @param string|null $customMessage
     *
     * @return HttpApiRequestException
     */
    public static function fromErrorResponse(HttpResponse $response, string $customMessage = null): HttpApiRequestException
    {
        $responseBody = json_decode($response->getBody(), true);

        $instance = new static(
            $customMessage ?? $response->getBody(),
            $response->getStatus()
        );

        if ($responseBody && array_key_exists('errors', $responseBody)) {
            $instance->errors = $responseBody['errors'];
        }

        return $instance;
    }

    /**
     * @return string[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
