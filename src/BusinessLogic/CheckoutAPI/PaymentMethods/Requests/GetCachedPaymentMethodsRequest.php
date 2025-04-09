<?php

namespace SeQura\Core\BusinessLogic\CheckoutAPI\PaymentMethods\Requests;

use SeQura\Core\Infrastructure\Data\DataTransferObject;

/**
 * Class GetCachedPaymentMethodsRequest.
 *
 * @package SeQura\Core\BusinessLogic\CheckoutAPI\PaymentMethods\Requests
 */
class GetCachedPaymentMethodsRequest extends DataTransferObject
{
    /**
     * @var string
     */
    protected $merchantId;

    /**
     * @param string $merchantId
     */
    public function __construct(string $merchantId)
    {
        $this->merchantId = $merchantId;
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
     */
    public function setMerchantId(string $merchantId): void
    {
        $this->merchantId = $merchantId;
    }

    /**
     * Create a GetAvailablePaymentMethodsRequest instance from an array.
     *
     * @param mixed[] $data
     *
     * @return GetCachedPaymentMethodsRequest
     */
    public static function fromArray(array $data): self
    {
        return new self(
            self::getDataValue($data, 'merchant_id')
        );
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $data['merchant_id'] = $this->merchantId;

        return $data;
    }
}
