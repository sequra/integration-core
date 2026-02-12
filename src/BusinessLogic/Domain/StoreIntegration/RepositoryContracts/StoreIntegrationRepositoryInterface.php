<?php

namespace SeQura\Core\BusinessLogic\Domain\StoreIntegration\RepositoryContracts;

use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models\StoreIntegration;

interface StoreIntegrationRepositoryInterface
{
    /**
     * @param StoreIntegration $storeIntegration
     *
     * @return void
     */
    public function setStoreIntegration(StoreIntegration $storeIntegration): void;

    /**
     * @return void
     */
    public function deleteStoreIntegration(): void;

    /**
     * @return string
     */
    public function getWebhookSignature(): string;

    /**
     * @return StoreIntegration
     */
    public function getStoreIntegration(): StoreIntegration;
}
