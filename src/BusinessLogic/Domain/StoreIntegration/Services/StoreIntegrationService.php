<?php

namespace SeQura\Core\BusinessLogic\Domain\StoreIntegration\Services;

use SeQura\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use SeQura\Core\BusinessLogic\Domain\HMAC\HMAC;
use SeQura\Core\BusinessLogic\Domain\Integration\StoreIntegration\StoreIntegrationServiceInterface as IntegrationsStoreServiceInterface;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Exceptions\CapabilitiesEmptyException;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models\Capability;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models\CreateStoreIntegrationRequest;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models\DeleteStoreIntegrationRequest;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\ProxyContracts\StoreIntegrationsProxyInterface;
use SeQura\Core\BusinessLogic\Domain\URL\Model\Query;
use SeQura\Core\BusinessLogic\Domain\URL\Model\URL;

/**
 * Class StoreIntegrationService.
 *
 * @package SeQura\Core\BusinessLogic\Domain\StoreIntegration\Services
 */
class StoreIntegrationService
{
    /**
     * @var IntegrationsStoreServiceInterface $integrationService
     */
    protected $integrationService;

    /**
     * @var StoreIntegrationsProxyInterface $storeIntegrationsProxy
     */
    protected $storeIntegrationsProxy;

    /**
     * @param IntegrationsStoreServiceInterface $integrationService
     * @param StoreIntegrationsProxyInterface $storeIntegrationsProxy
     */
    public function __construct(
        IntegrationsStoreServiceInterface $integrationService,
        StoreIntegrationsProxyInterface $storeIntegrationsProxy
    ) {
        $this->integrationService = $integrationService;
        $this->storeIntegrationsProxy = $storeIntegrationsProxy;
    }

    /**
     * Creates store integration.
     * Returns integration id.
     *
     * @param ConnectionData $connectionData
     *
     * @return string
     *
     * @throws CapabilitiesEmptyException
     */
    public function createStoreIntegration(ConnectionData $connectionData): string
    {
        $webhookUrl = $this->getWebhookUrl($connectionData);
        $capabilities = $this->getSupportedCapabilities();
        $response = $this->storeIntegrationsProxy->createStoreIntegration(
            new CreateStoreIntegrationRequest($connectionData, $webhookUrl, $capabilities)
        );

        return $response->getIntegrationId();
    }

    /**
     * @param ConnectionData $connectionData
     *
     * @return void
     */
    public function deleteStoreIntegration(ConnectionData $connectionData): void
    {
        $this->storeIntegrationsProxy->deleteStoreIntegration(
            new DeleteStoreIntegrationRequest($connectionData)
        );
    }

    /**
     * @return Capability[]
     *
     * @throws CapabilitiesEmptyException
     */
    protected function getSupportedCapabilities(): array
    {
        $capabilities = $this->integrationService->getSupportedCapabilities();

        if (empty($capabilities)) {
            throw new CapabilitiesEmptyException();
        }

        return $capabilities;
    }

    /**
     * Gets and signs webhook url.
     *
     * @param ConnectionData $connectionData
     *
     * @return URL
     */
    protected function getWebhookUrl(ConnectionData $connectionData): URL
    {
        $url = $this->integrationService->getWebhookUrl();

        $signatureQuery = new Query(
            'signature',
            HMAC::generateHMAC(
                [
                    StoreContext::getInstance()->getStoreId(),
                    $url->getPath(),
                    $connectionData->getAuthorizationCredentials()->getUsername(),
                    $connectionData->getMerchantId()
                ],
                $connectionData->getAuthorizationCredentials()->getPassword()
            )
        );

        $url->addQuery($signatureQuery);
        $url->addQuery(new Query('storeId', StoreContext::getInstance()->getStoreId()));

        return $url;
    }
}
