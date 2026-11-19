<?php

namespace SeQura\Core\BusinessLogic\Domain\StoreIntegration\Services;

use SeQura\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use SeQura\Core\BusinessLogic\Domain\Connection\RepositoryContracts\ConnectionDataRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Connection\RepositoryContracts\CredentialsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\HMAC\HMAC;
use SeQura\Core\BusinessLogic\Domain\Integration\StoreInfo\StoreInfoServiceInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\StoreInfo\StoreUrlProviderInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\StoreIntegration\StoreIntegrationServiceInterface as IntegrationsStoreServiceInterface;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Exceptions\CapabilitiesEmptyException;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Exceptions\StoreIntegrationNotFoundException;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models\Capability;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models\CreateStoreIntegrationRequest;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models\DeleteStoreIntegrationRequest;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\ProxyContracts\StoreIntegrationsProxyInterface;
use SeQura\Core\BusinessLogic\Domain\URL\Exceptions\InvalidUrlException;
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
     * @var CredentialsRepositoryInterface $credentialsRepository
     */
    protected $credentialsRepository;

    /**
     * @param IntegrationsStoreServiceInterface $integrationService
     * @param StoreIntegrationsProxyInterface $storeIntegrationsProxy
     * @param ConnectionDataRepositoryInterface $connectionDataRepository
     * @param StoreInfoServiceInterface $storeInfoService
     * @param CredentialsRepositoryInterface $credentialsRepository
     */
    public function __construct(
        IntegrationsStoreServiceInterface $integrationService,
        StoreIntegrationsProxyInterface $storeIntegrationsProxy,
        ConnectionDataRepositoryInterface $connectionDataRepository,
        StoreInfoServiceInterface $storeInfoService,
        CredentialsRepositoryInterface $credentialsRepository
    ) {
        $this->integrationService = $integrationService;
        $this->storeIntegrationsProxy = $storeIntegrationsProxy;
        $this->connectionDataRepository = $connectionDataRepository;
        $this->storeInfoService = $storeInfoService;
        $this->credentialsRepository = $credentialsRepository;
    }

    /**
     * Creates store integration.
     *
     * @param ConnectionData $connectionData
     *
     * @return string Id of the registered integration in the portal
     *
     * @throws CapabilitiesEmptyException
     * @throws InvalidUrlException
     */
    public function createStoreIntegration(ConnectionData $connectionData): string
    {
        $signature = $this->computeSignature($connectionData);
        $webhookUrl = $this->buildWebhookUrl($this->integrationService->getWebhookUrl(), $signature);
        $capabilities = $this->getRegistrableCapabilities($connectionData);

        return $this->storeIntegrationsProxy->createStoreIntegration(
            new CreateStoreIntegrationRequest($connectionData, $webhookUrl, $capabilities)
        )->getIntegrationId();
    }

    /**
     * @param ConnectionData $connectionData
     *
     * @return void
     *
     * @throws InvalidUrlException
     */
    public function deleteStoreIntegration(ConnectionData $connectionData): void
    {
        $signature = $this->computeSignature($connectionData);
        $webhookUrl = $this->buildWebhookUrl($this->integrationService->getWebhookUrl(), $signature);

        $this->storeIntegrationsProxy->deleteStoreIntegration(
            new DeleteStoreIntegrationRequest($connectionData, $webhookUrl->buildUrl())
        );
    }

    public function isExpressCheckoutRegistrable(ConnectionData $connectionData): bool
    {
        return self::containsExpressCheckout($this->integrationService->getSupportedCapabilities())
            && $this->isCaasAllowed($connectionData);
    }

    /**
     * @return Capability[]
     *
     * @throws CapabilitiesEmptyException
     */
    protected function getRegistrableCapabilities(ConnectionData $connectionData): array
    {
        $capabilities = $this->getSupportedCapabilities();

        if ($this->isCaasAllowed($connectionData)) {
            return $capabilities;
        }

        return array_values(array_filter($capabilities, static function (Capability $capability) {
            return !self::isExpressCheckout($capability);
        }));
    }

    protected function isCaasAllowed(ConnectionData $connectionData): bool
    {
        foreach ($this->credentialsRepository->getCredentials() as $credentials) {
            if ($credentials->getDeployment() === $connectionData->getDeployment() && $credentials->isAllowedForCaas()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Capability[] $capabilities
     */
    private static function containsExpressCheckout(array $capabilities): bool
    {
        foreach ($capabilities as $capability) {
            if (self::isExpressCheckout($capability)) {
                return true;
            }
        }

        return false;
    }

    private static function isExpressCheckout(Capability $capability): bool
    {
        return $capability->getCapability() === Capability::expressCheckout()->getCapability();
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
     *
     * @throws InvalidUrlException
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
            $this->resolveStoreUrl(),
        ];
    }

    /**
     * Resolves the storeUrl used in the HMAC payload.
     *
     * Prefers {@see StoreUrlProviderInterface::getStoreUrl()} when the injected
     * StoreInfo service implements it — that path is cheap and avoids building
     * the full StoreInfo payload on every register / delete / webhook
     * validation. Falls back to {@see StoreInfoServiceInterface::getStoreInfo()}
     * for plugins that have not opted in, preserving backwards compatibility.
     *
     * @return string
     */
    private function resolveStoreUrl(): string
    {
        if ($this->storeInfoService instanceof StoreUrlProviderInterface) {
            return $this->storeInfoService->getStoreUrl();
        }

        return $this->storeInfoService->getStoreInfo()->getStoreUrl();
    }
}
