<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\Shop;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\Domain\Product\Model\ShopProduct;

/**
 * Class ShopProductsResponse
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\Shop
 */
class ShopProductsResponse extends Response
{
    /**
     * @var ShopProduct[]
     */
    protected $products;

    /**
     * @param ShopProduct[] $products
     */
    public function __construct(array $products)
    {
        $this->products = $products;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return array_map(function (ShopProduct $product) {
            return $product->toArray();
        }, $this->products);
    }
}
