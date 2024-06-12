<?php

namespace SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Tracking;

use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidTimestampException;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\OrderRequestDTO;
use SeQura\Core\BusinessLogic\Utility\StringValidator;

/**
 * Class Tracking
 *
 * @package SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Tracking
 */
abstract class Tracking extends OrderRequestDTO
{
    /**
     * @var string Tracking type.
     */
    protected $type;

    /**
     * @var string Tracking reference.
     */
    protected $reference;

    /**
     * @var string|null Tracking number.
     */
    protected $trackingNumber;

    /**
     * @var string|null When this delivery was handed.
     */
    protected $deliveredAt;

    /**
     * @param string $type
     * @param string $reference
     * @param string|null $trackingNumber
     * @param string|null $deliveredAt
     *
     * @throws InvalidTimestampException
     */
    protected function __construct(
        string $type,
        string $reference,
        string $trackingNumber = null,
        string $deliveredAt = null
    ) {
        if ($deliveredAt && !StringValidator::isISO8601Timestamp($deliveredAt)) {
            throw new InvalidTimestampException('Delivered at must be ISO 8601 formatted timestamp.');
        }

        $this->type = $type;
        $this->reference = $reference;
        $this->trackingNumber = $trackingNumber;
        $this->deliveredAt = $deliveredAt;
    }

    /**
     * Creates a new Tracking instance from the given array.
     *
     * @param array $data An array with data to create a new instance.
     *
     * @return static A new Tracking instance.
     *
     * @throws InvalidTimestampException If delivered at timestamp is invalid.
     */
    public static function fromArray(array $data): self
    {
        $type = static::getDataValue($data, 'type');
        $reference = static::getDataValue($data, 'reference');
        $trackingNumber = static::getDataValue($data, 'tracking_number');
        $deliveredAt = static::getDataValue($data, 'delivered_at');

        return new static($type, $reference, $trackingNumber, $deliveredAt);
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getReference(): string
    {
        return $this->reference;
    }

    /**
     * @return string|null
     */
    public function getTrackingNumber(): ?string
    {
        return $this->trackingNumber;
    }

    /**
     * @return string|null
     */
    public function getDeliveredAt(): ?string
    {
        return $this->deliveredAt;
    }
}
