<?php

namespace SeQura\Core\BusinessLogic\Domain\Integration\Product;

use SeQura\Core\BusinessLogic\Domain\Product\Model\ShopProduct;

/**
 * Interface ProductServiceInterface.
 *
 * @package SeQura\Core\BusinessLogic\Domain\Integration\Product
 */
interface ProductServiceInterface
{
    /**
     * Returns products SKU based on product ID.
     * Returns null if product is not supported on integration level.
     *
     * @param string $productId
     *
     * @return string
     */
    public function getProductsSkuByProductId(string $productId): ?string;

    /**
     * Returns true if product is virtual.
     *
     * @param string $productId
     *
     * @return bool
     */
    public function isProductVirtual(string $productId): bool;

    /**
     * Returns all categories related to product whose id is given as first parameter.
     *
     * @param string $productId
     *
     * @return string[]
     */
    public function getProductCategoriesByProductId(string $productId): array;

    /**
     * Gets all shop products with their basic information.
     *
     * @param int $page
     * @param int $limit
     * @param string $search
     *
     * @return ShopProduct[]
     */
    public function getShopProducts(int $page, int $limit, string $search): array;

    /**
     * @param string[] $ids
     *
     * @return ShopProduct[]
     */
    public function getShopProductByIds(array $ids): array;
}
