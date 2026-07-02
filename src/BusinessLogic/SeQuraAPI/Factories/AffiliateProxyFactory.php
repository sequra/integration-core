<?php

namespace SeQura\Core\BusinessLogic\SeQuraAPI\Factories;

use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionDataNotFoundException;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\CredentialsNotFoundException;
use SeQura\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use SeQura\Core\BusinessLogic\Domain\Deployments\Exceptions\DeploymentNotFoundException;
use SeQura\Core\BusinessLogic\Domain\Deployments\Services\DeploymentsService;
use SeQura\Core\BusinessLogic\SeQuraAPI\BaseProxy;
use SeQura\Core\Infrastructure\Http\HttpClient;

/**
 * Class AffiliateProxyFactory.
 *
 * Builds a base (unauthenticated) proxy pointed at the merchant's deployment base URL. Affiliate
 * postbacks must NOT carry the seQura Basic Auth header: the runtime router forwards the request
 * verbatim to a third party (the affiliate network), so sending the connection credentials would
 * leak them. Credentials for the affiliate network travel inside the payload instead.
 *
 * @package SeQura\Core\BusinessLogic\SeQuraAPI\Factories
 */
class AffiliateProxyFactory
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
     * @return BaseProxy
     *
     * @throws ConnectionDataNotFoundException
     * @throws CredentialsNotFoundException
     * @throws DeploymentNotFoundException
     */
    public function build(string $merchantId): BaseProxy
    {
        $connectionData = $this->connectionService->getConnectionDataByMerchantId($merchantId);
        $deployment = $this->deploymentsService->getDeploymentById($connectionData->getDeployment());

        $baseUrl = $connectionData->getEnvironment() === BaseProxy::LIVE_MODE ?
            $deployment->getLiveDeploymentURL()->getApiBaseUrl() :
            (BaseProxy::getSandboxApiBaseUrlOverride() ?: $deployment->getSandboxDeploymentURL()->getApiBaseUrl());

        return new BaseProxy($this->client, $baseUrl);
    }
}
