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
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            'error' => $this->error->getMessage(),
        ];
    }

    /**
     * @return Throwable
     */
    public function getError(): Throwable
    {
        return $this->error;
    }
}
