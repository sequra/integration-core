<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\Response;

/**
 * Class Response
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\Response
 */
abstract class Response
{
    /**
     * @var bool
     */
    protected $successful = true;

    /**
     * Returns whether a response is successful.
     *
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return $this->successful;
    }

    /**
     * Transforms response to array.
     *
     * @return array Array representation of response object.
     */
    abstract public function toArray(): array;
}
