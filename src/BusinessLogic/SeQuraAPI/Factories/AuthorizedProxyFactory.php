<?php

namespace SeQura\Core\BusinessLogic\SeQuraAPI\Factories;

use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionDataNotFoundException;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\CredentialsNotFoundException;
use SeQura\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use SeQura\Core\BusinessLogic\Domain\Deployments\Exceptions\DeploymentNotFoundException;
use SeQura\Core\BusinessLogic\Domain\Deployments\Services\DeploymentsService;
use SeQura\Core\BusinessLogic\SeQuraAPI\Authorization\AuthorizedProxy;
use SeQura\Core\Infrastructure\Http\HttpClient;

/**
 * Class AuthorizedProxyFactory.
 *
 * @package SeQura\Core\BusinessLogic\SeQuraAPI\Factories
 */
class AuthorizedProxyFactory
{
    /**
     * @var HttpClient $client
     */
    private $client;

    /**
     * @var ConnectionService $connectionService
     */
    private $connectionService;

    /**
     * @var DeploymentsService $deploymentsService
     */
    private $deploymentsService;

    /**
     * @param HttpClient $httpClient
     * @param ConnectionService $connectionService
     * @param DeploymentsService $deploymentsService
     */
    public function __construct(
        HttpClient $httpClient,
        ConnectionService $connectionService,
        DeploymentsService $deploymentsService
    ) {
        $this->client = $httpClient;
        $this->connectionService = $connectionService;
        $this->deploymentsService = $deploymentsService;
    }

    /**
     * @param string $merchantId
     *
     * @return AuthorizedProxy
     *
     * @throws ConnectionDataNotFoundException
     * @throws CredentialsNotFoundException
     * @throws DeploymentNotFoundException
     */
    public function build(string $merchantId): AuthorizedProxy
    {
        $connectionData = $this->connectionService->getConnectionDataByMerchantId($merchantId);
        $deployment = $this->deploymentsService->getDeploymentById($connectionData->getDeployment());

        return new AuthorizedProxy($this->client, $connectionData, $deployment, $merchantId);
    }
}
