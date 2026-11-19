<?php

namespace SeQura\Core\BusinessLogic\Domain\Product\Model;

/**
 * Class ShopProduct.
 *
 * @package SeQura\Core\BusinessLogic\Domain\Product\Model
 */
class ShopProduct
{
    /**
     * @var string $id
     */
    private $id;
    /**
     * @var string $sku
     */
    private $sku;
    /**
     * @var string $name
     */
    private $name;

    /**
     * @param string $id
     * @param string $sku
     * @param string $name
     */
    public function __construct(string $id, string $sku, string $name)
    {
        $this->id = $id;
        $this->sku = $sku;
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getSku(): string
    {
        return $this->sku;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'name' => $this->name,
        ];
    }
}
