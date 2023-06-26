<?php

namespace SeQura\Core\BusinessLogic\Domain\Product\Models;

use SeQura\Core\BusinessLogic\Domain\Product\Exceptions\EmptyProductParameterException;

/**
 * Class Product
 *
 * @package SeQura\Core\BusinessLogic\Domain\Product\Models
 */
class Product
{
    /**
     * @var string
     */
    private $productId;

    /**
     * @var string
     */
    private $productName;

    /**
     * @param string $productId
     * @param string $productName
     *
     * @throws EmptyProductParameterException
     */
    public function __construct(string $productId, string $productName)
    {
        if(empty($productId) || empty($productName)) {
            throw new EmptyProductParameterException('No parameter can be an empty string.');
        }

        $this->productId = $productId;
        $this->productName = $productName;
    }

    /**
     * @return string
     */
    public function getProductId(): string
    {
        return $this->productId;
    }

    /**
     * @return string
     */
    public function getProductName(): string
    {
        return $this->productName;
    }
}
