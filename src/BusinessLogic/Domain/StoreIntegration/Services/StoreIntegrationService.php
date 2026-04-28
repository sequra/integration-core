<?php

namespace SeQura\Core\BusinessLogic\Domain\StoreIntegration\Services;

use SeQura\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use SeQura\Core\BusinessLogic\Domain\Connection\RepositoryContracts\ConnectionDataRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\HMAC\HMAC;
use SeQura\Core\BusinessLogic\Domain\Integration\StoreInfo\StoreInfoServiceInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\StoreIntegration\StoreIntegrationServiceInterface as IntegrationsStoreServiceInterface;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Exceptions\CapabilitiesEmptyException;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Exceptions\StoreIntegrationNotFoundException;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models\Capability;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models\CreateStoreIntegrationRequest;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models\DeleteStoreIntegrationRequest;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\ProxyContracts\StoreIntegrationsProxyInterface;
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
     * @var ConnectionDataRepositoryInterface $connectionDataRepository
     */
    protected $connectionDataRepository;

    /**
     * @var StoreInfoServiceInterface $storeInfoService
     */
    protected $storeInfoService;

    /**
     * @param IntegrationsStoreServiceInterface $integrationService
     * @param StoreIntegrationsProxyInterface $storeIntegrationsProxy
     * @param ConnectionDataRepositoryInterface $connectionDataRepository
     * @param StoreInfoServiceInterface $storeInfoService
     */
    public function __construct(
        IntegrationsStoreServiceInterface $integrationService,
        StoreIntegrationsProxyInterface $storeIntegrationsProxy,
        ConnectionDataRepositoryInterface $connectionDataRepository,
        StoreInfoServiceInterface $storeInfoService
    ) {
        $this->integrationService = $integrationService;
        $this->storeIntegrationsProxy = $storeIntegrationsProxy;
        $this->connectionDataRepository = $connectionDataRepository;
        $this->storeInfoService = $storeInfoService;
    }

    /**
     * Creates store integration.
     *
     * @param ConnectionData $connectionData
     *
     * @return void
     *
     * @throws CapabilitiesEmptyException
     */
    public function createStoreIntegration(ConnectionData $connectionData): void
    {
        $signature = $this->computeSignature($connectionData);
        $webhookUrl = $this->buildWebhookUrl($this->integrationService->getWebhookUrl(), $signature);
        $capabilities = $this->getSupportedCapabilities();

        $this->storeIntegrationsProxy->createStoreIntegration(
            new CreateStoreIntegrationRequest($connectionData, $webhookUrl, $capabilities)
        );
    }

    /**
     * @param ConnectionData $connectionData
     *
     * @return void
     */
    public function deleteStoreIntegration(ConnectionData $connectionData): void
    {
        $signature = $this->computeSignature($connectionData);
        $webhookUrl = $this->buildWebhookUrl($this->integrationService->getWebhookUrl(), $signature);

        $this->storeIntegrationsProxy->deleteStoreIntegration(
            new DeleteStoreIntegrationRequest($connectionData, $webhookUrl->buildUrl())
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
     * @param URL $webhookUrl
     * @param string $signature
     *
     * @return URL
     */
    protected function buildWebhookUrl(URL $webhookUrl, string $signature): URL
    {
        $storeId = StoreContext::getInstance()->getStoreId();
        $signedUrl = new URL($webhookUrl->getPath(), $webhookUrl->getQueries());
        $signedUrl->addQuery(new Query('storeId', $storeId));
        $signedUrl->addQuery(new Query('signature', $signature));

        return $signedUrl;
    }

    /**
     * @return string
     *
     * @throws StoreIntegrationNotFoundException
     */
    public function getWebhookSignature(): string
    {
        $connectionSettings = $this->connectionDataRepository->getAllConnectionSettings();

        if (empty($connectionSettings)) {
            throw new StoreIntegrationNotFoundException();
        }

        return $this->computeSignature($connectionSettings[0]);
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
        $connectionSettings = $this->connectionDataRepository->getAllConnectionSettings();

        if (empty($connectionSettings)) {
            throw new StoreIntegrationNotFoundException();
        }

        $payload = $this->signaturePayload();

        foreach ($connectionSettings as $connectionData) {
            $secret = $connectionData->getAuthorizationCredentials()->getPassword();
            if (HMAC::validateHMAC($payload, $secret, $webhookSignature)) {
                return;
            }
        }

        throw new InvalidSignatureException('Webhook signature mismatch.', 400);
    }

    /**
     * @param ConnectionData $connectionData
     *
     * @return string
     */
    private function computeSignature(ConnectionData $connectionData): string
    {
        return HMAC::generateHMAC(
            $this->signaturePayload(),
            $connectionData->getAuthorizationCredentials()->getPassword()
        );
    }

    /**
     * @return string[]
     */
    private function signaturePayload(): array
    {
        return [
            StoreContext::getInstance()->getStoreId(),
            $this->storeInfoService->getStoreInfo()->getStoreUrl(),
        ];
    }
}
