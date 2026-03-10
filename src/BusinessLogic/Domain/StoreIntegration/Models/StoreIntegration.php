<?php

namespace SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models;

use SeQura\Core\Infrastructure\Data\DataTransferObject;

/**
 * Class StoreIntegration
 *
 * @package SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models
 */
class StoreIntegration extends DataTransferObject
{
    /**
     * @var string
     */
    protected $storeId;

    /**
     * @var string
     */
    protected $signature;

    /**
     * @var string
     */
    protected $integrationId;

    /**
     * @var string
     */
    protected $webhookUrl;

    /**
     * @param string $storeId
     * @param string $signature
     * @param string $integrationId
     * @param string $webhookUrl
     */
    public function __construct(string $storeId, string $signature, string $integrationId, string $webhookUrl)
    {
        $this->storeId = $storeId;
        $this->signature = $signature;
        $this->integrationId = $integrationId;
        $this->webhookUrl = $webhookUrl;
    }

    /**
     * @return string
     */
    public function getStoreId(): string
    {
        return $this->storeId;
    }

    /**
     * @param string $storeId
     *
     * @return void
     */
    public function setStoreId(string $storeId): void
    {
        $this->storeId = $storeId;
    }

    /**
     * @return string
     */
    public function getSignature(): string
    {
        return $this->signature;
    }

    /**
     * @param string $signature
     *
     * @return void
     */
    public function setSignature(string $signature): void
    {
        $this->signature = $signature;
    }

    /**
     * @return string
     */
    public function getIntegrationId(): string
    {
        return $this->integrationId;
    }

    /**
     * @param string $integrationId
     *
     * @return void
     */
    public function setIntegrationId(string $integrationId): void
    {
        $this->integrationId = $integrationId;
    }

    /**
     * @return string
     */
    public function getWebhookUrl(): string
    {
        return $this->webhookUrl;
    }

    /**
     * @param string $webhookUrl
     *
     * @return void
     */
    public function setWebhookUrl(string $webhookUrl): void
    {
        $this->webhookUrl = $webhookUrl;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            'storeIntegration' => [
                'storeId' => $this->storeId,
                'signature' => $this->signature,
                'integrationId' => $this->integrationId,
                'webhookUrl' => $this->webhookUrl
            ]
        ];
    }
}
