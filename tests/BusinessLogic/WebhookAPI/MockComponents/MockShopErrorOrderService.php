<?php

namespace SeQura\Core\Tests\BusinessLogic\WebhookAPI\MockComponents;

use SeQura\Core\BusinessLogic\Webhook\Services\ShopOrderService;
use SeQura\Core\Infrastructure\Http\Exceptions\HttpRequestException;

class MockShopErrorOrderService implements ShopOrderService
{
    /**
     * @param string $orderId
     * @param string $status
     * @param int|null $reasonCode
     * @param string|null $message
     * @throws HttpRequestException
     */
    public function updateStatus(
        string $orderId,
        string $status,
        ?int $reasonCode = null,
        ?string $message = null
    ): void
    {
        throw new HttpRequestException('Error updating shop order status.');
    }
}
