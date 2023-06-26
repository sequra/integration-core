<?php

namespace SeQura\Core\BusinessLogic\Domain\Integration\Store;

use SeQura\Core\BusinessLogic\Domain\Stores\Models\Store;

/**
 * Interface StoreServiceInterface
 *
 * @package SeQura\Core\BusinessLogic\Domain\Integration\Store
 */
interface StoreServiceInterface
{
    /**
     * Returns shop domain/url.
     *
     * @return string
     */
    public function getStoreDomain(): string;

    /**
     * Returns all stores within a multiple environment.
     *
     * @return Store[]
     */
    public function getStores(): array;

    /**
     * Returns current active store.
     *
     * @return Store|null
     */
    public function getDefaultStore(): ?Store;

    /**
     * Returns Store object based on id given as first parameter.
     *
     * @param string $id
     *
     * @return Store|null
     */
    public function getStoreById(string $id): ?Store;
}
