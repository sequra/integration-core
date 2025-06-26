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
        $errors = self::getErrorMessages($response);

        $instance = new static(
            !empty($errors) ? join('. ', $errors) : $customMessage,
            $response->getStatus()
        );

        $instance->errors = $errors;

        return $instance;
    }

    /**
     * Extracts errors from the HTTP response body.
     *
     * @param HttpResponse $response
     *
     * @return string[]
     */
    private static function getErrorMessages(HttpResponse $response): array
    {
        $errors = [];
        $responseBody = json_decode($response->getBody(), true);
        if (!is_array($responseBody) || !isset($responseBody['errors'])) {
            return $errors;
        }
        foreach ($responseBody['errors'] as $error) {
            if (is_string($error)) {
                $errors[] = $error;
            } elseif (is_array($error) && isset($error['title'])) {
                $errors[] = $error['title'];
            }
        }

        return $errors;
    }

    /**
     * @return string[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
