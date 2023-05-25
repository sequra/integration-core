<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\Response;

use SeQura\Core\Infrastructure\Data\DataTransferObject;

/**
 * Class Response
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\Response
 */
abstract class Response extends DataTransferObject
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
}
