<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\Response;

use Throwable;

/**
 * Class ErrorResponse
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\Response
 */
class ErrorResponse extends Response
{
    /**
     * @inheritdoc
     */
    protected $successful = false;

    /**
     * @var Throwable
     */
    protected $error;

    /**
     * ErrorResponse constructor.
     *
     * @param Throwable $error
     */
    public function __construct(Throwable $error)
    {
        $this->error = $error;
    }

    /**
     * Implementation is swallowing all undefined calls to avoid undefined method call exceptions when
     *
     * @param $methodName
     * @param $arguments
     *
     * @see ErrorHandlingAspect already hanled the API call exception but because of chaining calle will trigger
     * API controller messages on instance of the @see self.
     *
     * @return self Already handled error response
     */
    public function __call($methodName, $arguments)
    {
        return $this;
    }

    /**
     * Creates an ErrorResponse instance from a Throwable.
     *
     * @param Throwable $e
     *
     * @return self
     */
    public static function fromError(Throwable $e): self
    {
        return new static($e);
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            'errorCode' => $this->error->getCode(),
            'errorMessage' => $this->error->getMessage(),
        ];
    }
}
