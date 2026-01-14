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
        $this->addQueriesToWebhookUrl($url, $connectionData);

        return $url;
    }

    /**
     * Adds storeId and signature to webhook url.
     *
     * @param URL $webhookUrl
     * @param ConnectionData $connectionData
     *
     * @return void
     */
    protected function addQueriesToWebhookUrl(URL $webhookUrl, ConnectionData $connectionData): void
    {
        $webhookUrl->addQuery(new Query('storeId', StoreContext::getInstance()->getStoreId()));
        $webhookUrl->addQuery(new Query('signature', $this->generateWebhookSignature($webhookUrl, $connectionData)));
    }

    /**
     * Generates a webhook signature using HMAC based on the provided URL and connection data.
     *
     * @param URL $webhookUrl The URL of the webhook for which the signature is generated.
     * @param ConnectionData $connectionData The connection data containing authorization credentials and merchant
     * information.
     *
     * @return string The generated HMAC-based signature for the webhook.
     */
    protected function generateWebhookSignature(URL $webhookUrl, ConnectionData $connectionData): string
    {
        return HMAC::generateHMAC(
            [
                StoreContext::getInstance()->getStoreId(),
                $webhookUrl->getPath(),
                $connectionData->getAuthorizationCredentials()->getUsername(),
                $connectionData->getMerchantId()
            ],
            $connectionData->getAuthorizationCredentials()->getPassword()
        );
    }
}
