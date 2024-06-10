<?php

namespace SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models;

/**
 * Class WidgetLocation
 *
 * @package SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models
 */
class WidgetLocation
{
    /**
     * CSS selector for retrieving the element where the widget should be inserted.
     *
     * @var string
     */
    private $selForTarget;

     /**
     * The seQura product identifier.
     *
     * @var string
     */
    private $product;

     /**
     * The country identifier.
     *
     * @var string
     */
    private $country;

    /**
     * Constructor.
     */
    public function __construct(string $selForTarget, string $product, string $country)
    {
        $this->selForTarget = $selForTarget;
        $this->product = $product;
        $this->country = $country;
    }

    public function getSelForTarget(): string
    {
        return $this->selForTarget;
    }

    public function getProduct(): string
    {
        return $this->product;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function setSelForTarget(string $selForTarget): void
    {
        $this->selForTarget = $selForTarget;
    }

    public function setProduct(string $product): void
    {
        $this->product = $product;
    }

    public function setCountry(string $country): void
    {
        $this->country = $country;
    }

    public static function fromArray(array $data): ?self
    {
        if (!isset($data['selForTarget']) || !isset($data['product']) || !isset($data['country'])) {
            return null;
        }

        return new self(
            $data['selForTarget'],
            $data['product'],
            $data['country']
        );
    }

    public function toArray(): array
    {
        return [
            'selForTarget' => $this->selForTarget,
            'product' => $this->product,
            'country' => $this->country
        ];
    }
}
