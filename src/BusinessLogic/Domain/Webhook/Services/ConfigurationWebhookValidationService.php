<?php

namespace SeQura\Core\BusinessLogic\Domain\Webhook\Services;

use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionDataNotFoundException;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\CredentialsNotFoundException;
use SeQura\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use SeQura\Core\BusinessLogic\Domain\HMAC\HMAC;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Services\StoreIntegrationService;
use SeQura\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
use SeQura\Core\BusinessLogic\Domain\Webhook\Exceptions\WebhookSignatureValidationFailed;
use SeQura\Core\BusinessLogic\Domain\Webhook\Models\WebhookValidationRequest;

/**
 * Class ConfigurationWebhookValidationService.
 *
 * @package SeQura\Core\BusinessLogic\Domain\Webhook\Services
 */
class ConfigurationWebhookValidationService
{
    /**
     * @var ConnectionService $connectionService
     */
    protected $connectionService;

    /**
     * @var StoreIntegrationService $storeIntegrationService
     */
    protected $storeIntegrationService;

    /**
     * @param ConnectionService $connectionService
     * @param StoreIntegrationService $storeIntegrationService
     */
    public function __construct(
        ConnectionService $connectionService,
        StoreIntegrationService $storeIntegrationService
    ) {
        $this->connectionService = $connectionService;
        $this->storeIntegrationService = $storeIntegrationService;
    }

    /**
     * @param WebhookValidationRequest $webhookValidationRequest
     *
     * @return void
     *
     * @throws WebhookSignatureValidationFailed
     * @throws ConnectionDataNotFoundException
     * @throws CredentialsNotFoundException
     */
    public function validateWebhookSignature(WebhookValidationRequest $webhookValidationRequest): void
    {
        $connectionData = $this->connectionService->getConnectionDataByMerchantId($webhookValidationRequest->getMerchantId());
        $payload = $this->storeIntegrationService
            ->getExpectedWebhookSignaturePayload($connectionData);

        if (!HMAC::validateHMAC(
            $payload,
            $connectionData->getAuthorizationCredentials()->getPassword(),
            $webhookValidationRequest->getWebhookSignature()
        )) {
            throw new WebhookSignatureValidationFailed(
                new TranslatableLabel('Webhook signature validation failed.', 'webhook.signature.validation.failed')
            );
        }
    }
}
