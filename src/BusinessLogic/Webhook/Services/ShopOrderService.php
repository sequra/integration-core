<?php

namespace SeQura\Core\BusinessLogic\Webhook\Services;

use SeQura\Core\BusinessLogic\Domain\Webhook\Models\Webhook;

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
     * @param Webhook $orderId
     * @param string $status
     *
     * @return mixed
     */
    public function updateStatus(Webhook $webhook, string $status);
}
