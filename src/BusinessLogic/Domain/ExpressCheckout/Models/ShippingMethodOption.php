<?php

namespace SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Models;

/**
 * @package SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Models
 */
class ShippingMethodOption
{
    /**
     * @var string
     */
    protected $reference;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var int
     */
    protected $costWithTax;

    /**
     * @var string
     */
    protected $description;

    /**
     * @param string $reference
     * @param string $name
     * @param int $costWithTax
     * @param string $description
     */
    public function __construct(string $reference, string $name, int $costWithTax, string $description)
    {
        $this->reference = $reference;
        $this->name = $name;
        $this->costWithTax = $costWithTax;
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getReference(): string
    {
        return $this->reference;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getCostWithTax(): int
    {
        return $this->costWithTax;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }
}
