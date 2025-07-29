<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\Disconnect\Requests;

/**
 * Class DisconnectRequest.
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\Disconnect\Requests
 */
class DisconnectRequest
{
    /**
     * @var string $deploymentId
     */
    private $deploymentId;
    /**
     * @var bool $isFullDisconnect
     */
    private $isFullDisconnect;

    /**
     * @param string $deploymentId
     * @param bool $isFullDisconnect
     */
    public function __construct(string $deploymentId, bool $isFullDisconnect)
    {
        $this->deploymentId = $deploymentId;
        $this->isFullDisconnect = $isFullDisconnect;
    }

    /**
     * @return string
     */
    public function getDeploymentId(): string
    {
        return $this->deploymentId;
    }

    /**
     * @return bool
     */
    public function isFullDisconnect(): bool
    {
        return $this->isFullDisconnect;
    }
}
