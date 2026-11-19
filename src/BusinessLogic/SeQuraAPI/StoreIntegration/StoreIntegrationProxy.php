<?php

namespace SeQura\Core\BusinessLogic\SeQuraAPI\StoreIntegration;

use SeQura\Core\BusinessLogic\Domain\Deployments\Exceptions\DeploymentNotFoundException;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Exceptions\InvalidLocationHeaderException;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Exceptions\LocationHeaderEmptyException;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models\CreateStoreIntegrationRequest;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models\CreateStoreIntegrationResponse;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models\DeleteStoreIntegrationRequest;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models\DeleteStoreIntegrationResponse;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\ProxyContracts\StoreIntegrationsProxyInterface;
use SeQura\Core\BusinessLogic\SeQuraAPI\Factories\ConnectionProxyFactory;
use SeQura\Core\BusinessLogic\SeQuraAPI\StoreIntegration\Requests\CreateStoreIntegrationHttpRequest;
use SeQura\Core\BusinessLogic\SeQuraAPI\StoreIntegration\Requests\DeleteStoreIntegrationHttpRequest;
use SeQura\Core\Infrastructure\Http\Exceptions\HttpRequestException;

/**
 * Class StoreIntegrationProxy.
 *
 * @package SeQura\Core\BusinessLogic\SeQuraAPI\StoreIntegration
 */
class StoreIntegrationProxy implements StoreIntegrationsProxyInterface
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
     * @param CreateStoreIntegrationRequest $request
     *
     * @return CreateStoreIntegrationResponse
     *
     * @throws DeploymentNotFoundException
     * @throws HttpRequestException
     * @throws InvalidLocationHeaderException
     * @throws LocationHeaderEmptyException
     */
    public function createStoreIntegration(CreateStoreIntegrationRequest $request): CreateStoreIntegrationResponse
    {
        $response = $this->connectionProxyFactory->buildAuthorizedProxy($request->getConnectionData())
            ->post(new CreateStoreIntegrationHttpRequest($request));

        $headers = array_change_key_case($response->getHeaders());
        $location = $headers['location'] ?? '';

        return CreateStoreIntegrationResponse::fromLocationHeader($location);
    }

    /**
     * @param DeleteStoreIntegrationRequest $request
     *
     * @return DeleteStoreIntegrationResponse
     *
     * @throws DeploymentNotFoundException
     * @throws HttpRequestException
     */
    public function deleteStoreIntegration(DeleteStoreIntegrationRequest $request): DeleteStoreIntegrationResponse
    {
        $this->connectionProxyFactory->buildAuthorizedProxy($request->getConnectionData())
            ->delete(new DeleteStoreIntegrationHttpRequest($request));

        return new DeleteStoreIntegrationResponse();
    }
}
