<?php

namespace SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest;

/**
 * Class DeliveryMethod
 *
 * @package SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest
 */
class DeliveryMethod extends OrderRequestDTO
{
    /**
     * @var string Name of the delivery method.
     */
    protected $name;

    /**
     * @var string|null Days it takes to deliver the goods.
     */
    protected $days;

    /**
     * @var string|null Company or agent that performs the delivery.
     */
    protected $provider;

    /**
     * @var boolean|null If goods are delivered to the buyer's home or office and not to a pick-up place.
     */
    protected $homeDelivery;

    /**
     * @param string $name
     * @param string|null $days
     * @param string|null $provider
     * @param bool|null $homeDelivery
     */
    public function __construct(string $name, string $days = null, string $provider = null, bool $homeDelivery = null)
    {
        $this->name = $name;
        $this->days = $days;
        $this->provider = $provider;
        $this->homeDelivery = $homeDelivery;
    }

    /**
     * Create a new DeliveryMethod instance from an array of data.
     *
     * @param array $data Array containing the data.
     *
     * @return DeliveryMethod Returns a new DeliveryMethod instance.
     */
    public static function fromArray(array $data): DeliveryMethod
    {
        return new self(
            self::getDataValue($data, 'name'),
            self::getDataValue($data, 'days', null),
            self::getDataValue($data, 'provider', null),
            self::getDataValue($data, 'home_delivery', null)
        );
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getDays(): ?string
    {
        return $this->days;
    }

    /**
     * @return string|null
     */
    public function getProvider(): ?string
    {
        return $this->provider;
    }

    /**
     * @return bool|null
     */
    public function getHomeDelivery(): ?bool
    {
        return $this->homeDelivery;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return $this->transformPropertiesToAnArray(get_object_vars($this));
    }
}
