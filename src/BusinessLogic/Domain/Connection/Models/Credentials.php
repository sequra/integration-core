<?php

namespace SeQura\Core\BusinessLogic\Domain\Connection\Models;

use SeQura\Core\Infrastructure\Data\DataTransferObject;

/**
 * Class Credentials.
 *
 * @package SeQura\Core\BusinessLogic\Domain\Connection\Models
 */
class Credentials extends DataTransferObject
{
    /**
     * @var string $merchantId
     */
    private $merchantId;

    /**
     * @var string $country
     */
    private $country;

    /**
     * @var string $currency
     */
    private $currency;

    /**
     * @var string $assetsKey
     */
    private $assetsKey;

    /**
     * @param string $merchantId
     * @param string $country
     * @param string $currency
     * @param string $assetsKey
     */
    public function __construct(string $merchantId, string $country, string $currency, string $assetsKey)
    {
        $this->merchantId = $merchantId;
        $this->country = $country;
        $this->currency = $currency;
        $this->assetsKey = $assetsKey;
    }

    /**
     * @return string
     */
    public function getMerchantId(): string
    {
        return $this->merchantId;
    }

    /**
     * @return string
     */
    public function getCountry(): string
    {
        return $this->country;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @return string
     */
    public function getAssetsKey(): string
    {
        return $this->assetsKey;
    }

    /**
     * @return array<string,string>
     */
    public function toArray(): array
    {
        return [
            'merchantId' => $this->merchantId,
            'country' => $this->country,
            'currency' => $this->currency,
            'assetsKey' => $this->assetsKey
        ];
    }

    /**
     * @param array<mixed, mixed> $data
     *
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['merchantId'] ?? '',
            $data['country'] ?? '',
            $data['currency'] ?? '',
            $data['assetsKey'] ?? ''
        );
    }
}
