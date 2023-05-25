<?php

namespace SeQura\Core\BusinessLogic\Webhook\Repositories;

/**
 * Class OrderStatusMappingRepository
 *
 * @package SeQura\Core\BusinessLogic\Webhook\Repositories
 */
interface OrderStatusMappingRepository
{
    /**
     * Returns OrderStatusMapping instance for current store context.
     *
     * @return array
     */
    public function getOrderStatusMapping(): array;

    /**
     * Insert/update OrderStatusMapping for current store context;
     *
     * @param array $orderStatusMapping
     *
     * @return void
     */
    public function setOrderStatusMapping(array $orderStatusMapping): void;
}
