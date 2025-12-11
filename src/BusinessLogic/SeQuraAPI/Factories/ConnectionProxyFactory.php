<?php

namespace SeQura\Core\BusinessLogic\SeQuraAPI\Factories;

use SeQura\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use SeQura\Core\BusinessLogic\Domain\Deployments\Exceptions\DeploymentNotFoundException;
use SeQura\Core\BusinessLogic\Domain\Deployments\Services\DeploymentsService;
use SeQura\Core\BusinessLogic\SeQuraAPI\Authorization\AuthorizedProxy;
use SeQura\Core\BusinessLogic\SeQuraAPI\BaseProxy;
use SeQura\Core\Infrastructure\Http\HttpClient;

/**
 * Class ConnectionProxyFactory.
 *
 * @package SeQura\Core\BusinessLogic\SeQuraAPI\Factories
 */
class ConnectionProxyFactory
{
    /**
     * @var HttpClient $client
     */
    private $client;

    /**
     * @var DeploymentsService $deploymentsService
     */
    private $deploymentsService;

    /**
     * @param HttpClient $httpClient
     * @param DeploymentsService $deploymentsService
     */
    public function __construct(
        HttpClient $httpClient,
        DeploymentsService $deploymentsService
    ) {
        $this->client = $httpClient;
        $this->deploymentsService = $deploymentsService;
    }

    /**
     * @param ConnectionData $connectionData
     *
     * @return BaseProxy
     *
     * @throws DeploymentNotFoundException
     */
    public function build(ConnectionData $connectionData): BaseProxy
    {
        $deployment = $this->deploymentsService->getDeploymentById($connectionData->getDeployment());

        return new BaseProxy(
            $this->client,
            $connectionData->getEnvironment() === BaseProxy::LIVE_MODE ?
            $deployment->getLiveDeploymentURL()->getApiBaseUrl() : $deployment->getSandboxDeploymentURL()->getApiBaseUrl()
        );
    }

    /**
     * @param ConnectionData $connectionData
     *
     * @return AuthorizedProxy
     *
     * @throws DeploymentNotFoundException
     */
    public function buildAuthorizedProxy(ConnectionData $connectionData): AuthorizedProxy
    {
        $deployment = $this->deploymentsService->getDeploymentById($connectionData->getDeployment());

        return new AuthorizedProxy($this->client, $connectionData, $deployment, $connectionData->getMerchantId());
    }
}
