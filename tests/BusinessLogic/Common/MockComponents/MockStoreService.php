<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use SeQura\Core\BusinessLogic\Domain\Integration\Store\StoreServiceInterface;
use SeQura\Core\BusinessLogic\Domain\Stores\Models\Store;

/**
 * Class MockStoreService
 *
 * @package SeQura\Core\Tests\BusinessLogic\Common\MockComponents
 */
class MockStoreService implements StoreServiceInterface
{
    public function getStoreDomain(): string
    {
        return '';
    }

    public function getStores(): array
    {
        return [
            new Store('1', 'Default store'),
            new Store('2', 'Test store 2')
        ];
    }

    public function getDefaultStore(): ?Store
    {
        return new Store('1', 'Default store');
    }

    public function getStoreById(string $id): ?Store
    {
        return new Store('2', 'Test store 2');
    }
}
