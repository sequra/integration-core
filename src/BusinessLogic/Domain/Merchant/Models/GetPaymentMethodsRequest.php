<?php

namespace SeQura\Core\BusinessLogic\Domain\Merchant\Models;

use SeQura\Core\Infrastructure\Data\DataTransferObject;

/**
 * Class GetPaymentMethodsRequest
 *
 * @package SeQura\Core\BusinessLogic\Domain\Merchant\Models
 */
class GetPaymentMethodsRequest extends DataTransferObject
{
    /**
     * @var string
     */
    protected $merchantId;
    /**
     * @var bool
     */
    protected $cache;

    /**
     * @param string $merchantId
     */
    public function __construct(string $merchantId, bool $cache = false)
    {
        $this->merchantId = $merchantId;
        $this->cache = $cache;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $data['merchant_id'] = $this->merchantId;
        $data['cache'] = $this->cache;

        return $data;
    }

    public function getMerchantId(): string
    {
        return $this->merchantId;
    }

    public function setMerchantId(string $merchantId): void
    {
        $this->merchantId = $merchantId;
    }

    public function isCache(): bool
    {
        return $this->cache;
    }

    public function setCache(bool $cache): void
    {
        $this->cache = $cache;
    }
}
