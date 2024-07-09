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
     * @param string $orderId
     */
    public function __construct(string $orderId)
    {
        $this->orderId = $orderId;
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
     * Create a GetAvailablePaymentMethodsRequest instance from an array.
     *
     * @param array $data
     *
     * @return GetAvailablePaymentMethodsRequest
     */
    public static function fromArray(array $data): self
    {
        return new self(
            self::getDataValue($data, 'order_id')
        );
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $data['order_id'] = $this->orderId;

        return $data;
    }
}
