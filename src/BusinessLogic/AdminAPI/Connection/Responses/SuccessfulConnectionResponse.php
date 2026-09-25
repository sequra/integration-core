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
     * @var array<string, string|null>
     */
    protected $portalUrls;

    /**
     * @param string|null $portalUrl URL of the SeQura portal the store is connected to
     * @param array<string, string|null> $portalUrls Portal URL of each connection, keyed by its deployment
     */
    public function __construct(?string $portalUrl = null, array $portalUrls = [])
    {
        $this->portalUrl = $portalUrl;
        $this->portalUrls = $portalUrls;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            'isValid' => true,
            'portalUrl' => $this->portalUrl,
            'portalUrls' => $this->portalUrls
        ];
    }
}
