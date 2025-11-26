<?php

namespace SeQura\Core\BusinessLogic\Domain\StoreIntegration\ProxyContracts;

use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models\CreateStoreIntegrationRequest;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models\CreateStoreIntegrationResponse;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models\DeleteStoreIntegrationRequest;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models\DeleteStoreIntegrationResponse;

/**
 * Interface StoreIntegrationsProxyInterface.
 *
 * @package SeQura\Core\BusinessLogic\Domain\StoreIntegration\ProxyContracts
 */
interface StoreIntegrationsProxyInterface
{
    /**
     * @param CreateStoreIntegrationRequest $request
     *
     * @return CreateStoreIntegrationResponse
     */
    public function createStoreIntegration(CreateStoreIntegrationRequest $request): CreateStoreIntegrationResponse;

    /**
     * @param DeleteStoreIntegrationRequest $request
     *
     * @return DeleteStoreIntegrationResponse
     */
    public function deleteStoreIntegration(DeleteStoreIntegrationRequest $request): DeleteStoreIntegrationResponse;
}
