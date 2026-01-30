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
     * @inheritDoc
     */
    public function getStoreInfo(): StoreInfo
    {
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
}
