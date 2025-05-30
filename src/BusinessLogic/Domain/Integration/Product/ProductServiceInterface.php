<?php

namespace SeQura\Core\BusinessLogic\Domain\Integration\Product;

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
}
