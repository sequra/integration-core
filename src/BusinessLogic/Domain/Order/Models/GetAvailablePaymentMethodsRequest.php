<?php

namespace SeQura\Core\BusinessLogic\Domain\Order\Models;

use SeQura\Core\Infrastructure\Data\DataTransferObject;

/**
 * Class GetAvailablePaymentMethodsRequest
 *
 * @package SeQura\Core\BusinessLogic\Domain\Order\Models
 */
class GetAvailablePaymentMethodsRequest extends DataTransferObject
{
    /**
     * @var string
     */
    protected $orderId;

    /**
     * @var string
     */
    protected $merchantId;

    /**
     * @param string $orderId
     * @param string $merchantId
     */
    public function __construct(string $orderId, string $merchantId)
    {
        $this->orderId = $orderId;
        $this->merchantId = $merchantId;
    }

    /**
     * @return string
     */
    public function getOrderId(): string
    {
        return $this->orderId;
    }

    /**
     * @param string $orderId
     */
    public function setOrderId(string $orderId): void
    {
        $this->orderId = $orderId;
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

    /**
     * Create a GetAvailablePaymentMethodsRequest instance from an array.
     *
     * @param mixed[] $data
     *
     * @return GetAvailablePaymentMethodsRequest
     */
    public static function fromArray(array $data): self
    {
        return new self(
            self::getDataValue($data, 'order_id'),
            self::getDataValue($data, 'merchant_id')
        );
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $data['order_id'] = $this->orderId;
        $data['merchant_id'] = $this->merchantId;

        return $data;
    }
}
