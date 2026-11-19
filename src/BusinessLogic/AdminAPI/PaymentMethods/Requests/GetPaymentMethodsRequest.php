<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\PaymentMethods\Requests;

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
     * @param bool $cache
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

    /**
     * @return string
     */
    public function getMerchantId(): string
    {
        return $this->merchantId;
    }

    /**
     * @return bool
     */
    public function isCache(): bool
    {
        return $this->cache;
    }
}
