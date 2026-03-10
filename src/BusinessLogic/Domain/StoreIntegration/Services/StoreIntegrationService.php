<?php

namespace SeQura\Core\BusinessLogic\Domain\StoreIntegration\Services;

use Exception;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use SeQura\Core\BusinessLogic\Domain\Integration\StoreIntegration\StoreIntegrationServiceInterface as IntegrationsStoreServiceInterface;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Exceptions\CapabilitiesEmptyException;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Exceptions\StoreIntegrationNotFoundException;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models\Capability;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models\CreateStoreIntegrationRequest;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models\DeleteStoreIntegrationRequest;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models\StoreIntegration;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\ProxyContracts\StoreIntegrationsProxyInterface;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\RepositoryContracts\StoreIntegrationRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\URL\Model\Query;
use SeQura\Core\BusinessLogic\Domain\URL\Model\URL;
use SeQura\Core\BusinessLogic\Webhook\Exceptions\InvalidSignatureException;

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
     * @var StoreIntegrationRepositoryInterface $storeIntegrationRepository
     */
    protected $storeIntegrationRepository;

    /**
     * @param IntegrationsStoreServiceInterface $integrationService
     * @param StoreIntegrationsProxyInterface $storeIntegrationsProxy
     * @param StoreIntegrationRepositoryInterface $storeIntegrationRepository
     */
    public function __construct(
        IntegrationsStoreServiceInterface $integrationService,
        StoreIntegrationsProxyInterface $storeIntegrationsProxy,
        StoreIntegrationRepositoryInterface $storeIntegrationRepository
    ) {
        $this->integrationService = $integrationService;
        $this->storeIntegrationsProxy = $storeIntegrationsProxy;
        $this->storeIntegrationRepository = $storeIntegrationRepository;
    }

    /**
     * Creates store integration.
     * Returns integration id.
     *
     * @param ConnectionData $connectionData
     *
     * @return void
     *
     * @throws CapabilitiesEmptyException
     * @throws Exception
     */
    public function createStoreIntegration(ConnectionData $connectionData): void
    {
        $existing = $this->storeIntegrationRepository->getStoreIntegration();

        $signature = bin2hex(random_bytes(32));
        if ($existing) {
            $signature = $existing->getSignature();
        }

        $webhookUrl = $this->buildWebhookUrl($this->integrationService->getWebhookUrl(), $signature);
        $capabilities = $this->getSupportedCapabilities();

        $response = $this->storeIntegrationsProxy->createStoreIntegration(
            new CreateStoreIntegrationRequest($connectionData, $webhookUrl, $capabilities)
        );

        if ($existing) {
            return;
        }

        $storeIntegration = new StoreIntegration(
            StoreContext::getInstance()->getStoreId(),
            $signature,
            $response->getIntegrationId(),
            $webhookUrl->buildUrl()
        );

        $this->setStoreIntegration($storeIntegration);
    }

    /**
     * @param ConnectionData $connectionData
     * @param StoreIntegration $storeIntegration
     *
     * @return void
     */
    public function deleteStoreIntegration(ConnectionData $connectionData, StoreIntegration $storeIntegration): void
    {
        $this->storeIntegrationsProxy->deleteStoreIntegration(
            new DeleteStoreIntegrationRequest($connectionData, $storeIntegration)
        );

        $this->storeIntegrationRepository->deleteStoreIntegration();
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
     * @param URL $webhookUrl
     * @param string $signature
     *
     * @return URL
     */
    protected function buildWebhookUrl(URL $webhookUrl, string $signature): URL
    {
        $storeId = StoreContext::getInstance()->getStoreId();
        $webhookUrl->addQuery(new Query('storeId', $storeId));
        $webhookUrl->addQuery(new Query('signature', $signature));

        return $webhookUrl;
    }

    /**
     * @return string
     *
     * @throws StoreIntegrationNotFoundException
     */
    public function getWebhookSignature(): string
    {
        $storeIntegration = $this->storeIntegrationRepository->getStoreIntegration();

        if (!$storeIntegration) {
            throw new StoreIntegrationNotFoundException();
        }

        return $storeIntegration->getSignature();
    }

    /**
     * @param string $webhookSignature
     *
     * @return void
     *
     * @throws InvalidSignatureException
     * @throws StoreIntegrationNotFoundException
     */
    public function validateWebhookSignature(string $webhookSignature): void
    {
        $storedSignature = $this->getWebhookSignature();

        if (!hash_equals($storedSignature, $webhookSignature)) {
            throw new InvalidSignatureException('Webhook signature mismatch.', 400);
        }
    }

    /**
     * @param StoreIntegration $storeIntegration
     *
     * @return void
     */
    private function setStoreIntegration(StoreIntegration $storeIntegration): void
    {
        $this->storeIntegrationRepository->setStoreIntegration($storeIntegration);
    }
}
