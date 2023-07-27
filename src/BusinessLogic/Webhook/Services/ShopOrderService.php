<?php

namespace SeQura\Core\BusinessLogic\Webhook\Services;

/**
 * Interface ShopOrderService
 *
 * @package SeQura\Core\BusinessLogic\Webhook\Services
 */
interface ShopOrderService
{
    /**
     * Updates status of the order in the shop system based on the provided status.
     *
     * @param string $orderId
     * @param string $status
     * @param int|null $reasonCode
     * @param string|null $message
     *
     * @return mixed
     */
    public function updateStatus(string $orderId, string $status, ?int $reasonCode = null, ?string $message = null);
}
