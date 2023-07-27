<?php

namespace SeQura\Core\Tests\BusinessLogic\WebhookAPI\MockComponents;

use SeQura\Core\BusinessLogic\Webhook\Services\ShopOrderService;

class MockShopOrderService implements ShopOrderService
{
    /**
     * @param string $orderId
     * @param string $status
     * @param int|null $reasonCode
     * @param string|null $message
     * @inheritDoc
     */
    public function updateStatus(
        string $orderId,
        string $status,
        ?int $reasonCode = null,
        ?string $message = null
    ): void
    {
    }
}
