<?php

namespace SeQura\Core\BusinessLogic\SeQuraAPI\Connection;

use SeQura\Core\BusinessLogic\Domain\Connection\Models\ValidateConnectionRequest;
use SeQura\Core\BusinessLogic\Domain\Connection\ProxyContracts\ConnectionProxyInterface;
use SeQura\Core\BusinessLogic\SeQuraAPI\BaseProxy;
use SeQura\Core\BusinessLogic\SeQuraAPI\Connection\Request\ValidateConnectionHttpRequest;

/**
 * Class ConnectionProxy
 *
 * @package SeQura\Core\BusinessLogic\SeQuraAPI\Connection
 */
class ConnectionProxy extends BaseProxy implements ConnectionProxyInterface
{
    /**
     * @inheritDoc
     */
    public function validateConnection(ValidateConnectionRequest $request): void
    {
        $this->mode = $request->getConnectionData()->getEnvironment();
        $this->get(new ValidateConnectionHttpRequest($request))->decodeBodyToArray();
    }
}
