<?php

namespace SeQura\Core\BusinessLogic\SeQuraAPI\Connection;

use SeQura\Core\BusinessLogic\Domain\Connection\Models\Credentials;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\CredentialsRequest;
use SeQura\Core\BusinessLogic\Domain\Connection\ProxyContracts\ConnectionProxyInterface;
use SeQura\Core\BusinessLogic\SeQuraAPI\Connection\Request\ValidateConnectionHttpRequest;
use SeQura\Core\BusinessLogic\SeQuraAPI\Factories\ConnectionProxyFactory;

/**
 * Class ConnectionProxy
 *
 * @package SeQura\Core\BusinessLogic\SeQuraAPI\Connection
 */
class ConnectionProxy implements ConnectionProxyInterface
{
    /**
     * @var ConnectionProxyFactory $connectionProxyFactory
     */
    private $connectionProxyFactory;

    /**
     * @param ConnectionProxyFactory $connectionProxyFactory
     */
    public function __construct(ConnectionProxyFactory $connectionProxyFactory)
    {
        $this->connectionProxyFactory = $connectionProxyFactory;
    }

    /**
     * @inheritDoc
     */
    public function getCredentials(CredentialsRequest $request): array
    {
        $credentialsResponse = $this->connectionProxyFactory->build($request->getConnectionData())
            ->get(new ValidateConnectionHttpRequest($request))->decodeBodyToArray();

        return array_map(
            function ($item) use ($request) {
                return new Credentials(
                    $item['ref'] ?? '',
                    $item['country'] ?? '',
                    $item['currency'] ?? '',
                    $item['assets_key'] ?? '',
                    $item,
                    $request->getConnectionData()->getDeployment()
                );
            },
            $credentialsResponse
        );
    }
}
