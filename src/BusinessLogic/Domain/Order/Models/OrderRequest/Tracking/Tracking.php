<?php

namespace SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Tracking;

use InvalidArgumentException;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidTimestampException;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\OrderRequestDTO;
use SeQura\Core\BusinessLogic\Utility\StringValidator;

/**
 * Class Tracking
 *
 * @package SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Tracking
 */
/**
 * @phpstan-consistent-constructor
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
        ?string $trackingNumber = null,
        ?string $deliveredAt = null
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
     * Creates the Tracking subtype the given array's type names.
     *
     * @param mixed[] $data An array with data to create a new instance.
     *
     * @return Tracking A new Tracking instance.
     *
     * @throws InvalidTimestampException If delivered at timestamp is invalid.
     * @throws InvalidArgumentException If the type is not a known tracking type.
     */
    public static function fromArray(array $data): self
    {
        $type = static::getDataValue($data, 'type');

        switch ($type) {
            case TrackingType::TYPE_POSTAL:
                return TrackingPostal::fromArray($data);
            case TrackingType::TYPE_PICKUP_POINT:
                return TrackingPickupPoint::fromArray($data);
            case TrackingType::TYPE_PICKUP_STORE:
                return TrackingPickupStore::fromArray($data);
            default:
                throw new InvalidArgumentException("Unknown tracking type: $type.");
        }
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
