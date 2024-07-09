<?php

namespace SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Tracking;

use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidTimestampException;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidUrlException;
use SeQura\Core\BusinessLogic\Utility\StringValidator;

/**
 * Class TrackingPostal
 *
 * @package SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Tracking
 */
class TrackingPostal extends Tracking
{
    /**
     * @var string The name of the company that handled this delivery.
     */
    protected $carrier;

    /**
     * @var string|null Tracking URL.
     */
    protected $trackingUrl;

    /**
     * @param string $reference
     * @param string $carrier
     * @param string|null $trackingNumber
     * @param string|null $deliveredAt
     * @param string|null $trackingUrl
     *
     * @throws InvalidTimestampException
     * @throws InvalidUrlException
     */
    public function __construct(
        string $reference,
        string $carrier,
        string $trackingNumber = null,
        string $deliveredAt = null,
        string $trackingUrl = null
    ) {
        if ($trackingUrl && !StringValidator::isValidUrl($trackingUrl)) {
            throw new InvalidUrlException('Tracking url must be a valid url.');
        }

        parent::__construct(TrackingType::TYPE_POSTAL, $reference, $trackingNumber, $deliveredAt);

        $this->carrier = $carrier;
        $this->trackingUrl = $trackingUrl;
    }

    /**
     * @return string
     */
    public function getCarrier(): string
    {
        return $this->carrier;
    }

    /**
     * @return string|null
     */
    public function getTrackingUrl(): ?string
    {
        return $this->trackingUrl;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return $this->transformPropertiesToAnArray(get_object_vars($this));
    }

    /**
     * Create a new TrackingPostal instance from an array of data.
     *
     * @param array $data
     *
     * @return Tracking
     *
     * @throws InvalidTimestampException
     * @throws InvalidUrlException
     */
    public static function fromArray(array $data): Tracking
    {
        return new self(
            self::getDataValue($data, 'reference'),
            self::getDataValue($data, 'carrier'),
            self::getDataValue($data, 'tracking_number', null),
            self::getDataValue($data, 'delivered_at', null),
            self::getDataValue($data, 'tracking_url', null)
        );
    }
}
