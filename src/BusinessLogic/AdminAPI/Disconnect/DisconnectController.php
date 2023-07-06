<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\Disconnect;

use SeQura\Core\BusinessLogic\AdminAPI\Disconnect\Responses\DisconnectResponse;

/**
 * Class DisconnectController
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\Disconnect
 */
class DisconnectController
{
    /**
     * Disconnects and removes necessary data of the merchant.
     *
     * @return DisconnectResponse
     */
    public function disconnect(): DisconnectResponse
    {
        return new DisconnectResponse();
    }
}
