<?php

namespace SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\RepositoryContracts;

use SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\Models\OrderStatusMapping;

/**
 * Interface OrderStatusSettingsRepositoryInterface
 *
 * @package SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\RepositoryContracts
 */
interface OrderStatusSettingsRepositoryInterface
{
    /**
     * Returns OrderStatusMappings for current store context.
     *
     * @return OrderStatusMapping[]|null
     */
    public function getOrderStatusMapping(): ?array;

    /**
     * Insert/update OrderStatusMappings for current store context;
     *
     * @param OrderStatusMapping[] $orderStatusMapping
     *
     * @return void
     */
    public function setOrderStatusMapping(array $orderStatusMapping): void;
}
