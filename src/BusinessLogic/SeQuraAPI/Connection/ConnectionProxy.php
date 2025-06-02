<?php

namespace SeQura\Core\BusinessLogic\SeQuraAPI\Connection;

use SeQura\Core\BusinessLogic\Domain\Connection\Models\Credentials;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\CredentialsRequest;
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
    public function getCredentials(CredentialsRequest $request): array
    {
        $this->mode = $request->getConnectionData()->getEnvironment();
        $credentialsResponse = $this->get(new ValidateConnectionHttpRequest($request))->decodeBodyToArray();

        return array_map(
            function ($item) {
                return new Credentials(
                    $item['ref'] ?? '',
                    $item['country'] ?? '',
                    $item['currency'] ?? '',
                    $item['assets_key'] ?? '',
                    $item
                );
            },
            $credentialsResponse
        );
    }
}
