<?php

namespace SeQura\Core\BusinessLogic\Domain\Integration\StoreInfo;

use SeQura\Core\BusinessLogic\Domain\Stores\Models\StoreInfo;

/**
 * Interface StoreInfoServiceInterface
 *
 * @package SeQura\Core\BusinessLogic\Domain\Integration\StoreInfo
 */
interface StoreInfoServiceInterface
{
    /**
     * Gets store information including platform details, versions, and installed plugins.
     *
     * @return StoreInfo
     */
    public function getStoreInfo(): StoreInfo;
}
