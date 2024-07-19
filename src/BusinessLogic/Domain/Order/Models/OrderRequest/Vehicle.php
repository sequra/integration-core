<?php

namespace SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest;

/**
 * Class Vehicle
 *
 * @package SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest
 */
class Vehicle extends OrderRequestDTO
{
    /**
     * @var string Plaque identification from customer's vehicle.
     */
    protected $plaque;

    /**
     * @var string|null Brand from customer's vehicle.
     */
    protected $brand;

    /**
     * @var string|null Model from customer's vehicle.
     */
    protected $model;

    /**
     * @var string|null Frame identification from customer's vehicle.
     */
    protected $frame;

    /**
     * @var string|null First registration date plaque from customer's vehicle.
     */
    protected $firstRegistrationDate;

    /**
     * @param string $plaque
     * @param string|null $brand
     * @param string|null $model
     * @param string|null $frame
     * @param string|null $firstRegistrationDate
     */
    public function __construct(
        string $plaque,
        string $brand = null,
        string $model = null,
        string $frame = null,
        string $firstRegistrationDate = null
    ) {
        $this->plaque = $plaque;
        $this->brand = $brand;
        $this->model = $model;
        $this->frame = $frame;
        $this->firstRegistrationDate = $firstRegistrationDate;
    }

    /**
     * @return string
     */
    public function getPlaque(): string
    {
        return $this->plaque;
    }

    /**
     * @return string|null
     */
    public function getBrand(): ?string
    {
        return $this->brand;
    }

    /**
     * @return string|null
     */
    public function getModel(): ?string
    {
        return $this->model;
    }

    /**
     * @return string|null
     */
    public function getFrame(): ?string
    {
        return $this->frame;
    }

    /**
     * @return string|null
     */
    public function getFirstRegistrationDate(): ?string
    {
        return $this->firstRegistrationDate;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return $this->transformPropertiesToAnArray(get_object_vars($this));
    }

    /**
     * Create a new Vehicle instance from an array of data.
     *
     * @param array $data
     *
     * @return Vehicle
     */
    public static function fromArray(array $data): Vehicle
    {
        return new Vehicle(
            self::getDataValue($data, 'plaque'),
            self::getDataValue($data, 'brand', null),
            self::getDataValue($data, 'model', null),
            self::getDataValue($data, 'frame', null),
            self::getDataValue($data, 'first_registration_date', null)
        );
    }
}
