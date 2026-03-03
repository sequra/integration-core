<?php

namespace SeQura\Core\BusinessLogic\Domain\StoreIntegration\RepositoryContracts;

use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models\StoreIntegration;

/**
 * Interface StoreIntegrationRepositoryInterface.
 *
 * @package SeQura\Core\BusinessLogic\Domain\StoreIntegration\RepositoryContracts
 */
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
     * @return ?StoreIntegration
     */
    public function getStoreIntegration(): ?StoreIntegration;
}
