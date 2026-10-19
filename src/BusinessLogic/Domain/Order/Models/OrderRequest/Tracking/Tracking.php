<?php

namespace SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Tracking;

use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidCodeException;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidTimestampException;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidTrackingTypeException;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidUrlException;
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
     * Tracking subtype for each known tracking type.
     */
    private const TYPE_CLASSES = [
        TrackingType::TYPE_POSTAL => TrackingPostal::class,
        TrackingType::TYPE_PICKUP_POINT => TrackingPickupPoint::class,
        TrackingType::TYPE_PICKUP_STORE => TrackingPickupStore::class,
    ];

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
     * @throws InvalidUrlException If the tracking url is invalid.
     * @throws InvalidCodeException If a pickup operator or store code is invalid.
     * @throws InvalidTrackingTypeException If the type is not a known tracking type.
     */
    public static function fromArray(array $data): self
    {
        $type = static::getDataValue($data, 'type');
        if (!\is_string($type) || !isset(self::TYPE_CLASSES[$type])) {
            throw new InvalidTrackingTypeException();
        }

        $class = self::TYPE_CLASSES[$type];

        return $class::fromArray($data);
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
