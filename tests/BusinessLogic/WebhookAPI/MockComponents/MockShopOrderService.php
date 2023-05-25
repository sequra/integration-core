<?php

namespace SeQura\Core\Tests\BusinessLogic\WebhookAPI\MockComponents;

use SeQura\Core\BusinessLogic\Webhook\Services\ShopOrderService;

class MockShopOrderService implements ShopOrderService
{
    /**
     * @inheritDoc
     */
    public function updateStatus(string $orderId, string $status)
    {
    }
}
