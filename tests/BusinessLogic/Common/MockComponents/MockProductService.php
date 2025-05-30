<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use SeQura\Core\BusinessLogic\Domain\Integration\Product\ProductServiceInterface;

/**
 * Class MockProductService.
 *
 * @package SeQura\Core\Tests\BusinessLogic\Common\MockComponents
 */
class MockProductService implements ProductServiceInterface
{
    /**
     * @var string
     */
    private $productSku = '';
    /**
     * @var bool
     */
    private $isProductVirtual = false;
    /**
     * @var string[]
     */
    private $productCategories = [];

    /**
     * @inheritDoc
     */
    public function getProductsSkuByProductId(string $productId): string
    {
        return $this->productSku;
    }

    /**
     * @inheritDoc
     */
    public function isProductVirtual(string $productId): bool
    {
        return $this->isProductVirtual;
    }

    /**
     * @inheritDoc
     */
    public function getProductCategoriesByProductId(string $productId): array
    {
        return $this->productCategories;
    }

    /**
     * @param string[] $productCategories
     *
     * @return void
     */
    public function setMockProductCategories(array $productCategories): void
    {
        $this->productCategories = $productCategories;
    }

    /**
     * @param bool $isProductVirtual
     *
     * @return void
     */
    public function setMockProductVirtual(bool $isProductVirtual): void
    {
        $this->isProductVirtual = $isProductVirtual;
    }

    /**
     * @param string $productSku
     */
    public function setMockProductSku(string $productSku): void
    {
        $this->productSku = $productSku;
    }
}
