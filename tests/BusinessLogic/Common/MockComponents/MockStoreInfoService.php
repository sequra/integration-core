<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use SeQura\Core\BusinessLogic\Domain\Integration\StoreInfo\StoreInfoServiceInterface;
use SeQura\Core\BusinessLogic\Domain\Stores\Models\StoreInfo;

/**
 * Class MockStoreInfoService.
 *
 * @package Common\MockComponents
 */
class MockStoreInfoService implements StoreInfoServiceInterface
{
    /**
     * @var ?StoreInfo $storeInfo
     */
    private $storeInfo;

    /**
     * @var int
     */
    private $getStoreInfoCallCount = 0;

    /**
     * @inheritDoc
     */
    public function getStoreInfo(): StoreInfo
    {
        $this->getStoreInfoCallCount++;

        if ($this->storeInfo) {
            return $this->storeInfo;
        }

        return new StoreInfo(
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            ''
        );
    }

    /**
     * @param StoreInfo $storeInfo
     *
     * @return void
     */
    public function setMockStoreInfo(StoreInfo $storeInfo): void
    {
        $this->storeInfo = $storeInfo;
    }

    /**
     * @return int
     */
    public function getStoreInfoCallCount(): int
    {
        return $this->getStoreInfoCallCount;
    }
}
