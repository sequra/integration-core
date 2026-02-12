<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models\StoreIntegration;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\RepositoryContracts\StoreIntegrationRepositoryInterface;

class MockStoreIntegrationRepository implements StoreIntegrationRepositoryInterface
{
    /**
     * @var StoreIntegration
     */
    private $storeIntegration;

    /**
     * @param StoreIntegration $storeIntegration
     * @return void
     */
    public function setStoreIntegration(StoreIntegration $storeIntegration): void
    {
        $this->storeIntegration = $storeIntegration;
    }

    /**
     * @return void
     */
    public function deleteStoreIntegration(): void
    {
        $this->storeIntegration = null;
    }

    /**
     * @return string
     */
    public function getWebhookSignature(): string
    {
        return $this->storeIntegration ? $this->storeIntegration->getSignature() : 'signature';
    }

    /**
     * @return StoreIntegration
     */
    public function getStoreIntegration(): StoreIntegration
    {
        return $this->storeIntegration ?? new StoreIntegration(
            '1',
            'signature',
            '4'
        );
    }
}