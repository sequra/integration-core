<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\Integration\Responses;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\Domain\Version\Models\Version;

/**
 * Class IntegrationVersionResponse
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\Integration\Responses
 */
class IntegrationVersionResponse extends Response
{
    /**
     * @var Version
     */
    protected $version;

    /**
     * @param Version $version
     */
    public function __construct(Version $version)
    {
        $this->version = $version;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            'current' => $this->version->getCurrent(),
            'new' => $this->version->getNew(),
            'downloadNewVersionUrl' => $this->version->getDownloadNewVersionUrl()
        ];
    }
}
