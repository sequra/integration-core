<?php

namespace SeQura\Core\BusinessLogic\Domain\Webhook\Services;

use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Exceptions\StoreIntegrationNotFoundException;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Services\StoreIntegrationService;
use SeQura\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
use SeQura\Core\BusinessLogic\Domain\Webhook\Exceptions\WebhookSignatureValidationFailed;
use SeQura\Core\BusinessLogic\Domain\Webhook\Models\WebhookValidationRequest;
use SeQura\Core\BusinessLogic\Webhook\Exceptions\InvalidSignatureException;

/**
 * Class ConfigurationWebhookValidationService.
 *
 * @package SeQura\Core\BusinessLogic\Domain\Webhook\Services
 */
class ConfigurationWebhookValidationService
{
    /**
     * @var StoreIntegrationService $storeIntegrationService
     */
    protected $storeIntegrationService;

    /**
     * @param StoreIntegrationService $storeIntegrationService
     */
    public function __construct(StoreIntegrationService $storeIntegrationService)
    {
        $this->storeIntegrationService = $storeIntegrationService;
    }

    /**
     * @param WebhookValidationRequest $webhookValidationRequest
     *
     * @return void
     *
     * @throws WebhookSignatureValidationFailed
     */
    public function validateWebhookSignature(WebhookValidationRequest $webhookValidationRequest): void
    {
        try {
            $this->storeIntegrationService->validateWebhookSignature(
                $webhookValidationRequest->getWebhookSignature()
            );
        } catch (InvalidSignatureException | StoreIntegrationNotFoundException $e) {
            throw new WebhookSignatureValidationFailed(
                new TranslatableLabel(
                    'Webhook signature validation failed.',
                    'webhook.signature.validation.failed'
                )
            );
        }
    }
}
