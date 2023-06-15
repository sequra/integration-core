<?php

namespace SeQura\Core\Tests\BusinessLogic\WebhookAPI\MockComponents;

use SeQura\Core\BusinessLogic\Webhook\Services\ShopOrderService;
use SeQura\Core\Infrastructure\Http\Exceptions\HttpRequestException;

class MockShopErrorOrderService implements ShopOrderService
{
    /**
     * @throws HttpRequestException
     */
    public function updateStatus(string $orderId, string $status)
    {
        throw new HttpRequestException('Error updating shop order status.');
    }
}
