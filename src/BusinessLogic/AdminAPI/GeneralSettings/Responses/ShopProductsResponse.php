<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\GeneralSettings\Responses;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\Domain\Product\Models\Product;

/**
 * Class ShopProductsResponse
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\GeneralSettings\Responses
 */
class ShopProductsResponse extends Response
{
    /**
     * @var Product[]
     */
    private $products;

    /**
     * @param Product[]|null $products
     */
    public function __construct(?array $products)
    {
        $this->products = $products;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $shopProducts = [];
        foreach ($this->products as $product) {
            $shopProducts[] = [
                'productId' => $product->getProductId(),
                'productName' => $product->getProductName()
            ];
        }

        return $shopProducts;
    }
}
