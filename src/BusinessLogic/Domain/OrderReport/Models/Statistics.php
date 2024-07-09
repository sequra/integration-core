<?php

namespace SeQura\Core\BusinessLogic\Domain\OrderReport\Models;

use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\OrderRequestDTO;

/**
 * Class Statistics
 *
 * @package SeQura\Core\BusinessLogic\Domain\OrderReport\Models\OrderRequest
 */
class Statistics extends OrderRequestDTO
{
    /**
     * @var OrderStatistics[]
     */
    protected $orders;

    /**
     * @param OrderStatistics[] $orders
     */
    public function __construct(array $orders)
    {
        $this->orders = $orders;
    }

    /**
     * Creates a new Statistics instance from an input array.
     *
     * @param array $data
     *
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $orders = array_map(static function ($orderStatistics) {
            return OrderStatistics::fromArray($orderStatistics);
        }, self::getDataValue($data, 'orders', []));

        return new self($orders);
    }

    /**
     * @return OrderReport[]
     */
    public function getOrders(): array
    {
        return $this->orders;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return $this->transformPropertiesToAnArray(get_object_vars($this));
    }
}
