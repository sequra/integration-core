<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\Integration\Responses;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;

/**
 * Class IntegrationVersionResponse
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\Integration\Responses
 */
class IntegrationVersionResponse extends Response
{
    /**
     * @var string
     */
    private $version;

    /**
     * @param string $version
     */
    public function __construct(string $version)
    {
        $this->version = $version;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            'version' => $this->version
        ];
    }
}
