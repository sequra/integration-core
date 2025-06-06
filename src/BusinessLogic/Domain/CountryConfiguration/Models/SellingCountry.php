<?php

namespace SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Models;

/**
 * Class SellingCountry
 *
 * @package SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Models
 */
class SellingCountry
{
    /**
     * @var string
     */
    protected $code;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $merchantId;

    /**
     * @param string $code
     * @param string $name
     * @param string $merchantId
     */
    public function __construct(string $code, string $name, string $merchantId)
    {
        $this->code = $code;
        $this->name = $name;
        $this->merchantId = $merchantId;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getMerchantId(): string
    {
        return $this->merchantId;
    }

    /**
     * @param string $merchantId
     *
     * @return void
     */
    public function setMerchantId(string $merchantId): void
    {
        $this->merchantId = $merchantId;
    }
}
