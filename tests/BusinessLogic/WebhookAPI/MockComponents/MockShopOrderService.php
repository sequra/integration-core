<?php

namespace SeQura\Core\Tests\BusinessLogic\WebhookAPI\MockComponents;

use SeQura\Core\BusinessLogic\Domain\Webhook\Models\Webhook;
use SeQura\Core\BusinessLogic\Webhook\Services\ShopOrderService;

class MockShopOrderService implements ShopOrderService
{
    /**
     * @inheritDoc
     */
    public function updateStatus(Webhook $webhook, string $status)
    {
    }
}
