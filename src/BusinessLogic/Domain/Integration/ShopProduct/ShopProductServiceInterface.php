<?php

namespace SeQura\Core\BusinessLogic\Domain\Integration\ShopProduct;

/**
 * Interface ShopProductServiceInterface
 *
 * @package SeQura\Core\BusinessLogic\Domain\Integration\ShopProduct
 */
interface ShopProductServiceInterface
{
    /**
     * Gets all shop products with their basic information.
     *
     * @return array<int, array{id: string, sku: string, name: string}>
     */
    public function getShopProducts(): array;
}
