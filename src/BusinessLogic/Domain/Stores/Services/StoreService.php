<?php

namespace SeQura\Core\BusinessLogic\Domain\Stores\Services;

use Exception;
use SeQura\Core\BusinessLogic\Domain\Connection\RepositoryContracts\ConnectionDataRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\Store\StoreServiceInterface;
use SeQura\Core\BusinessLogic\Domain\Stores\Exceptions\FailedToRetrieveStoresException;
use SeQura\Core\BusinessLogic\Domain\Stores\Models\Store;
use SeQura\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;

/**
 * Class StoreService
 *
 * @package SeQura\Core\BusinessLogic\Domain\Stores\Services
 */
class StoreService
{
    /**
     * @var ConnectionDataRepositoryInterface
     */
    protected $connectionDataRepository;

    /**
     * @var StoreServiceInterface
     */
    protected $integrationStoreService;

    public function __construct(
        StoreServiceInterface $integrationStoreService,
        ConnectionDataRepositoryInterface $connectionDataRepository
    ) {
        $this->connectionDataRepository = $connectionDataRepository;
        $this->integrationStoreService = $integrationStoreService;
    }

    /**
     * Returns all stores.
     *
     * @return Store[]
     *
     * @throws FailedToRetrieveStoresException
     */
    public function getStores(): array
    {
        try {
            return $this->integrationStoreService->getStores();
        } catch (Exception $e) {
            throw new FailedToRetrieveStoresException(new TranslatableLabel('Failed to retrieve stores.', 'general.errors.stores.failed'));
        }
    }

    /**
     * Returns first connected store. If it does not exist, default store is returned.
     *
     * @return Store|null
     *
     * @throws FailedToRetrieveStoresException
     */
    public function getCurrentStore(): ?Store
    {
        try {
            $firstConnectedStoreId = $this->getFirstConnectedStoreId();

            return $firstConnectedStoreId ? $this->integrationStoreService->getStoreById(
                $firstConnectedStoreId
            ) : $this->integrationStoreService->getDefaultStore();
        } catch (Exception $e) {
            throw new FailedToRetrieveStoresException(new TranslatableLabel('Failed to retrieve stores.', 'general.errors.stores.failed'));
        }
    }

    /**
     * Retrieves all connected stores ids.
     *
     * @return string[]
     */
    public function getConnectedStores(): array
    {
        return $this->connectionDataRepository->getAllConnectionSettingsStores();
    }

    /**
     * Returns ID of first store that was connected to SeQura. If there is no store connected, empty string is returned.
     *
     * @return string
     */
    protected function getFirstConnectedStoreId(): string
    {
        $oldestStoreId = $this->connectionDataRepository->getOldestConnectionSettingsStoreId();

        return $oldestStoreId ?? '';
    }
}
