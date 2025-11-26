<?php

namespace SeQura\Core\BusinessLogic\SeQuraAPI\StoreIntegration;

use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionDataNotFoundException;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\CredentialsNotFoundException;
use SeQura\Core\BusinessLogic\Domain\Deployments\Exceptions\DeploymentNotFoundException;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Exceptions\InvalidLocationHeaderException;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Exceptions\LocationHeaderEmptyException;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models\CreateStoreIntegrationRequest;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models\CreateStoreIntegrationResponse;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models\DeleteStoreIntegrationRequest;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models\DeleteStoreIntegrationResponse;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\ProxyContracts\StoreIntegrationsProxyInterface;
use SeQura\Core\BusinessLogic\SeQuraAPI\Factories\AuthorizedProxyFactory;
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
     * @var AuthorizedProxyFactory $authorizedProxyFactory
     */
    private $authorizedProxyFactory;

    /**
     * @param AuthorizedProxyFactory $authorizedProxyFactory
     */
    public function __construct(AuthorizedProxyFactory $authorizedProxyFactory)
    {
        $this->authorizedProxyFactory = $authorizedProxyFactory;
    }

    /**
     * @inheritDoc
     *
     * @param CreateStoreIntegrationRequest $request
     *
     * @return CreateStoreIntegrationResponse
     *
     * @throws ConnectionDataNotFoundException
     * @throws CredentialsNotFoundException
     * @throws DeploymentNotFoundException
     * @throws HttpRequestException
     * @throws InvalidLocationHeaderException
     * @throws LocationHeaderEmptyException
     */
    public function createStoreIntegration(CreateStoreIntegrationRequest $request): CreateStoreIntegrationResponse
    {
        $response = $this->authorizedProxyFactory->build($request->getMerchantId())
            ->post(new CreateStoreIntegrationHttpRequest($request));

        $headers = array_change_key_case($response->getHeaders());
        $location = $headers['location'] ?? '';

        return CreateStoreIntegrationResponse::fromLocationHeader($location);
    }

    /**
     * @inheritDoc
     *
     * @param DeleteStoreIntegrationRequest $request
     *
     * @return DeleteStoreIntegrationResponse
     *
     * @throws ConnectionDataNotFoundException
     * @throws CredentialsNotFoundException
     * @throws DeploymentNotFoundException
     * @throws HttpRequestException
     */
    public function deleteStoreIntegration(DeleteStoreIntegrationRequest $request): DeleteStoreIntegrationResponse
    {
        $this->authorizedProxyFactory->build($request->getMerchantId())
            ->delete(new DeleteStoreIntegrationHttpRequest($request));

        return new DeleteStoreIntegrationResponse();
    }
}
