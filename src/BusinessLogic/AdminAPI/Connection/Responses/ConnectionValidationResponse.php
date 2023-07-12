<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\Connection\Responses;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;

/**
 * Class ConnectionValidationResponse
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\Connection\Responses
 */
class ConnectionValidationResponse extends Response
{
    /**
     * @var bool
     */
    private $isValid;

    /**
     * @var string|null
     */
    private $reason;


    public function __construct(bool $isValid, ?string $reason = null)
    {
        $this->isValid = $isValid;
        $this->reason = $reason;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            'isValid' => $this->isValid,
            'reason' => $this->reason
        ];
    }
}
