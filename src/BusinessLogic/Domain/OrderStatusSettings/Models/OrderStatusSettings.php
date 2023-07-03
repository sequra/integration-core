<?php

namespace SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\Models;

/**
 * Class OrderStatusSettings
 *
 * @package SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\Models
 */
class OrderStatusSettings
{
    /**
     * @var OrderStatusMapping[]
     */
    private $orderStatusMappings;

    /**
     * @var bool
     */
    private $informCancellation;

    /**
     * @param OrderStatusMapping[] $orderStatusMappings
     * @param bool $informCancellation
     */
    public function __construct(array $orderStatusMappings, bool $informCancellation)
    {
        $this->orderStatusMappings = $orderStatusMappings;
        $this->informCancellation = $informCancellation;
    }

    /**
     * @return OrderStatusMapping[]
     */
    public function getOrderStatusMappings(): array
    {
        return $this->orderStatusMappings;
    }

    /**
     * @param OrderStatusMapping[] $orderStatusMappings
     */
    public function setOrderStatusMappings(array $orderStatusMappings): void
    {
        $this->orderStatusMappings = $orderStatusMappings;
    }

    /**
     * @return bool
     */
    public function isInformCancellation(): bool
    {
        return $this->informCancellation;
    }

    /**
     * @param bool $informCancellation
     */
    public function setInformCancellation(bool $informCancellation): void
    {
        $this->informCancellation = $informCancellation;
    }
}
