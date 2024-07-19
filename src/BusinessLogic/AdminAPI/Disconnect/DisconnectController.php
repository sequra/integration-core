<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\Disconnect;

use SeQura\Core\BusinessLogic\AdminAPI\Disconnect\Responses\DisconnectResponse;
use SeQura\Core\BusinessLogic\Domain\Disconnect\Services\DisconnectService;

/**
 * Class DisconnectController
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\Disconnect
 */
class DisconnectController
{
    /**
     * @var DisconnectService
     */
    protected $disconnectService;

    /**
     * @param DisconnectService $disconnectService
     */
    public function __construct(DisconnectService $disconnectService)
    {
        $this->disconnectService = $disconnectService;
    }

    /**
     * Disconnects integration and removes necessary data of the merchant.
     *
     * @return DisconnectResponse
     */
    public function disconnect(): DisconnectResponse
    {
        $this->disconnectService->disconnect();

        return new DisconnectResponse();
    }
}
