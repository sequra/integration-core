<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use SeQura\Core\BusinessLogic\Domain\Stores\Services\StoreService;

/**
 * Class MockDomainStoreService.
 *
 * @package SeQura\Core\Tests\BusinessLogic\Common\MockComponents
 */
class MockDomainStoreService extends StoreService
{
    /**
     * @var string[]
     */
    private $connectedStores = [];

    /**
     * @return string[]
     */
    public function getConnectedStores(): array
    {
        return $this->connectedStores;
    }

    /**
     * @param string[] $connectedStores
     *
     * @return void
     */
    public function setMockConnectedStores(array $connectedStores): void
    {
        $this->connectedStores = $connectedStores;
    }
}
