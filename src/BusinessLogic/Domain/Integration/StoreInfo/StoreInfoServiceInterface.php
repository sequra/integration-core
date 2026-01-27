<?php

namespace SeQura\Core\BusinessLogic\Domain\Integration\StoreInfo;

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
     * @return array{
     *     store_name: string,
     *     store_url: string,
     *     platform: string,
     *     platform_version: string,
     *     plugin_version: string,
     *     php_version: string,
     *     db: string,
     *     os: string,
     *     plugins: string[]
     * }
     */
    public function getStoreInfo(): array;
}
