<?php

namespace SeQura\Core\BusinessLogic\SeQuraAPI\Deployments;

use SeQura\Core\BusinessLogic\Domain\Deployments\Models\Deployment;
use SeQura\Core\BusinessLogic\Domain\Deployments\ProxyContracts\DeploymentsProxyInterface;
use SeQura\Core\BusinessLogic\SeQuraAPI\BaseProxy;
use SeQura\Core\BusinessLogic\SeQuraAPI\Deployments\Requests\GetDeploymentsRequest;
use SeQura\Core\Infrastructure\Http\Exceptions\HttpRequestException;
use SeQura\Core\Infrastructure\Http\HttpClient;

/**
 * Class DeploymentsProxy.
 *
 * @package SeQura\Core\BusinessLogic\SeQuraAPI\Deployments
 */
class DeploymentsProxy extends BaseProxy implements DeploymentsProxyInterface
{
    /**
 * @var string
*/
    protected const BASE_API_URL = 'sequrapi.com';

    /**
     * @param HttpClient $httpClient
     */
    public function __construct(HttpClient $httpClient)
    {
        parent::__construct($httpClient, self::LIVE_MODE);
    }

    /**
     * @return Deployment[]
     *
     * @throws HttpRequestException
     */
    public function getDeployments(): array
    {
        $deployments = $this->get(new GetDeploymentsRequest())->decodeBodyToArray();

        return Deployment::fromBatch($deployments);
    }
}
