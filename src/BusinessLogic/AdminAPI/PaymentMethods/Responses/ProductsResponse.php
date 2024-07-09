<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\PaymentMethods\Responses;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;

/**
 * Class ProductsResponse
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\PaymentMethods\Responses
 */
class ProductsResponse extends Response
{
    /**
     * @var string[]
     */
    protected $products;

    /**
     * @param string[] $products
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
