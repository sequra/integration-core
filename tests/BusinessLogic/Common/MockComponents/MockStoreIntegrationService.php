<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use SeQura\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models\StoreIntegration;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Services\StoreIntegrationService;

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
     * @var array $createdIntegrationIds
     */
    private $createdIntegrationIds = [];

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
     * @param StoreIntegration|null $storeIntegration
     *
     * @return void
     */
    public function deleteStoreIntegration(ConnectionData $connectionData, ?StoreIntegration $storeIntegration): void
    {
        $this->deleted = true;
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
        return $this->storeIntegrationRepository->getWebhookSignature();
    }
}
