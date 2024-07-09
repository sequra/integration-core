<?php

namespace SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Tracking;

use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidCodeException;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidTimestampException;
use SeQura\Core\BusinessLogic\Utility\StringValidator;

/**
 * Class TrackingPickupStore
 *
 * @package SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Tracking
 */
class TrackingPickupStore extends Tracking
{
    /**
     * @var string|null Permanent identifier for the operator that handed the delivery.
     */
    protected $operatorRef;

    /**
     * @var string|null Permanent identifier for the store that handled this delivery.
     */
    protected $storeRef;

    /**
     * @var string|null When this delivery was available in the store.
     */
    protected $availableAt;

    /**
     * @var string|null Address line 1 for the store.
     */
    protected $addressLine1;

    /**
     * @var string|null Address line 2 for the store.
     */
    protected $addressLine2;

    /**
     * @var string|null Postal code.
     */
    protected $postalCode;

    /**
     * @var string|null City.
     */
    protected $city;

    /**
     * @var string|null State or region.
     */
    protected $state;

    /**
     * @var string|null Country code.
     */
    protected $countryCode;

    /**
     * @param string $reference
     * @param string|null $trackingNumber
     * @param string|null $deliveredAt
     * @param string|null $operatorRef
     * @param string|null $storeRef
     * @param string|null $availableAt
     * @param string|null $addressLine1
     * @param string|null $addressLine2
     * @param string|null $postalCode
     * @param string|null $city
     * @param string|null $state
     * @param string|null $countryCode
     *
     * @throws InvalidCodeException
     * @throws InvalidTimestampException
     */
    public function __construct(
        string $reference,
        string $trackingNumber = null,
        string $deliveredAt = null,
        string $operatorRef = null,
        string $storeRef = null,
        string $availableAt = null,
        string $addressLine1 = null,
        string $addressLine2 = null,
        string $postalCode = null,
        string $city = null,
        string $state = null,
        string $countryCode = null
    ) {
        if ($countryCode && !StringValidator::isStringLengthBetween($countryCode, 2, 3)) {
            throw new InvalidCodeException('Country code must be ISO 3166 formatted code.');
        }

        if ($availableAt && !StringValidator::isISO8601Timestamp($availableAt)) {
            throw new InvalidTimestampException('Available at must be ISO 8601 formatted timestamp.');
        }

        parent::__construct(TrackingType::TYPE_PICKUP_STORE, $reference, $trackingNumber, $deliveredAt);

        $this->operatorRef = $operatorRef;
        $this->storeRef = $storeRef;
        $this->availableAt = $availableAt;
        $this->addressLine1 = $addressLine1;
        $this->addressLine2 = $addressLine2;
        $this->postalCode = $postalCode;
        $this->city = $city;
        $this->state = $state;
        $this->countryCode = $countryCode;
    }

    /**
     * @return string|null
     */
    public function getOperatorRef(): ?string
    {
        return $this->operatorRef;
    }

    /**
     * @return string|null
     */
    public function getStoreRef(): ?string
    {
        return $this->storeRef;
    }

    /**
     * @return string|null
     */
    public function getAvailableAt(): ?string
    {
        return $this->availableAt;
    }

    /**
     * @return string|null
     */
    public function getAddressLine1(): ?string
    {
        return $this->addressLine1;
    }

    /**
     * @return string|null
     */
    public function getAddressLine2(): ?string
    {
        return $this->addressLine2;
    }

    /**
     * @return string|null
     */
    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    /**
     * @return string|null
     */
    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     * @return string|null
     */
    public function getState(): ?string
    {
        return $this->state;
    }

    /**
     * @return string|null
     */
    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return $this->transformPropertiesToAnArray(get_object_vars($this));
    }

    /**
     * Create a new TrackingPickupStore instance from an array of data.
     *
     * @param array $data
     *
     * @return Tracking
     *
     * @throws InvalidCodeException
     * @throws InvalidTimestampException
     */
    public static function fromArray(array $data): Tracking
    {
        return new self(
            self::getDataValue($data, 'reference'),
            self::getDataValue($data, 'tracking_number', null),
            self::getDataValue($data, 'delivered_at', null),
            self::getDataValue($data, 'operator_ref', null),
            self::getDataValue($data, 'store_ref', null),
            self::getDataValue($data, 'available_at', null),
            self::getDataValue($data, 'address_line_1', null),
            self::getDataValue($data, 'address_line_2', null),
            self::getDataValue($data, 'postal_code', null),
            self::getDataValue($data, 'city', null),
            self::getDataValue($data, 'state', null),
            self::getDataValue($data, 'country_code', null)
        );
    }
}
