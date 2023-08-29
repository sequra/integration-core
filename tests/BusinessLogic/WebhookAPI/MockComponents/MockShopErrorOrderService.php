<?php

namespace SeQura\Core\Tests\BusinessLogic\WebhookAPI\MockComponents;

use SeQura\Core\BusinessLogic\Domain\Webhook\Models\Webhook;
use SeQura\Core\BusinessLogic\Webhook\Services\ShopOrderService;
use SeQura\Core\Infrastructure\Http\Exceptions\HttpRequestException;

class MockShopErrorOrderService implements ShopOrderService
{
    /**
     * @throws HttpRequestException
     */
    public function updateStatus(Webhook $webhook, string $status): void
    {
        throw new HttpRequestException('Error updating shop order status.');
    }

    /**
     * @inheritDoc
     */
    public function getReportOrderIds(int $page, int $limit = 5000): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getStatisticsOrderIds(int $page, int $limit = 5000): array
    {
        return [];
    }
}
