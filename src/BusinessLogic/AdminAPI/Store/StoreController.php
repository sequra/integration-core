<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\Store;

use SeQura\Core\BusinessLogic\AdminAPI\Store\Responses\StoreResponse;
use SeQura\Core\BusinessLogic\AdminAPI\Store\Responses\StoresResponse;
use SeQura\Core\BusinessLogic\Domain\Stores\Exceptions\FailedToRetrieveStoresException;
use SeQura\Core\BusinessLogic\Domain\Stores\Models\Store;
use SeQura\Core\BusinessLogic\Domain\Stores\Services\StoreService;

/**
 * Class StoreController
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\Store
 */
class StoreController
{
    /**
     * @var StoreService
     */
    protected $storeService;

    /**
     * @param StoreService $storeService
     */
    public function __construct(StoreService $storeService)
    {
        $this->storeService = $storeService;
    }

    /**
     * Returns all shop stores.
     *
     * @return StoresResponse
     *
     * @throws FailedToRetrieveStoresException
     */
    public function getStores(): StoresResponse
    {
        return new StoresResponse($this->storeService->getStores());
    }

    /**
     * Returns a current active store.
     *
     * @return StoreResponse
     *
     * @throws FailedToRetrieveStoresException
     */
    public function getCurrentStore(): StoreResponse
    {
        $currentStore = $this->storeService->getCurrentStore();

        return $currentStore ? new StoreResponse($currentStore) : new StoreResponse($this->failBackStore());
    }

    /**
     * Creates failBack store in case there is no connected and default store.
     *
     * @return Store
     */
    protected function failBackStore(): Store
    {
        return new Store('failBack', 'failBack');
    }
}
