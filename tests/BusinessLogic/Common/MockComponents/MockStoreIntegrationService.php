<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use SeQura\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Services\StoreIntegrationService;
use SeQura\Core\BusinessLogic\Webhook\Exceptions\InvalidSignatureException;
use Throwable;

/**
 * Class StoreIntegrationService.
 *
 * @package SeQura\Core\Tests\BusinessLogic\Common\MockComponents
 */
class MockStoreIntegrationService extends StoreIntegrationService
{
    /**
     * @var bool $deleted
     */
    private $deleted = false;

    /**
     * @var string $signature
     */
    private $signature = 'testSignature';

    /**
     * @var array $createdIntegrationIds
     */
    private $createdIntegrationIds = [];

    /**
     * @var Throwable|null $deleteException
     */
    private $deleteException;

    /**
     * @param ConnectionData $connectionData
     *
     * @return void
     */
    public function createStoreIntegration(ConnectionData $connectionData): void
    {
        $this->createdIntegrationIds[$connectionData->getMerchantId()] = true;
    }

    /**
     * @param ConnectionData $connectionData
     *
     * @return void
     *
     * @throws Throwable When configured via setDeleteException().
     */
    public function deleteStoreIntegration(ConnectionData $connectionData): void
    {
        if ($this->deleteException !== null) {
            throw $this->deleteException;
        }
        $this->deleted = true;
    }

    /**
     * Configure the mock to throw on the next deleteStoreIntegration() call.
     *
     * @param Throwable $exception
     *
     * @return void
     */
    public function setDeleteException(Throwable $exception): void
    {
        $this->deleteException = $exception;
    }

    /**
     * @return bool
     */
    public function isDeleted(): bool
    {
        return $this->deleted;
    }

    /**
     * @return array
     */
    public function getCreatedIntegrationIds(): array
    {
        return $this->createdIntegrationIds;
    }

    /**
     * @return string
     */
    public function getWebhookSignature(): string
    {
        return $this->signature;
    }

    /**
     * @param string $webhookSignature
     *
     * @return void
     *
     * @throws InvalidSignatureException
     */
    public function validateWebhookSignature(string $webhookSignature): void
    {
        if (!hash_equals($this->signature, $webhookSignature)) {
            throw new InvalidSignatureException('Webhook signature mismatch.', 400);
        }
    }

    /**
     * @param string $signature
     *
     * @return void
     */
    public function setMockSignature(string $signature): void
    {
        $this->signature = $signature;
    }
}
