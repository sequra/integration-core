<?php

namespace SeQura\Core\BusinessLogic\Domain\Integration\Store;

/**
 * StoreIdProvider
 *
 * @package SeQura\Core\BusinessLogic\Domain\Integration\Store
 */
class StoreIdProvider
{
    /**
     * Override to provide current store ID according to the platform context.
     *
     * @return string
     */
    public function getCurrentStoreId(): string
    {
        return '';
    }
}
