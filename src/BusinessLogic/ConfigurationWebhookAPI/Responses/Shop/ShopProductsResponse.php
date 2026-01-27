<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\Shop;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;

/**
 * Class ShopProductsResponse
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\Shop
 */
class ShopProductsResponse extends Response
{
    /**
     * @var bool
     */
    protected $successful = true;

    /**
     * @var array<int, array{id: string, sku: string, name: string}>
     */
    protected $products;

    /**
     * @param array<int, array{id: string, sku: string, name: string}> $products
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
        return $this->products;
    }
}
