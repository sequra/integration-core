<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\Connection\Responses;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;

/**
 * Class ConnectionResponse
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\Connection\Responses
 */
class SuccessfulConnectionResponse extends Response
{
    /**
     * @var string|null
     */
    protected $portalUrl;

    /**
     * @param string|null $portalUrl URL of the SeQura portal the store is connected to
     */
    public function __construct(?string $portalUrl = null)
    {
        $this->portalUrl = $portalUrl;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            'isValid' => true,
            'portalUrl' => $this->portalUrl
        ];
    }
}
