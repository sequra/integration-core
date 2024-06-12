<?php

namespace SeQura\Core\Tests\BusinessLogic\WebhookAPI\MockComponents;

use SeQura\Core\BusinessLogic\Domain\Webhook\Models\Webhook;
use SeQura\Core\BusinessLogic\Webhook\Services\ShopOrderService;
use SeQura\Core\Infrastructure\Http\Exceptions\HttpRequestException;

class MockShopErrorOrderService implements ShopOrderService
{
    /**
     * @param Webhook $webhook
     * @param string $status
     * @param int|null $reasonCode
     * @param string|null $message
     * @throws HttpRequestException
     */
    public function updateStatus(
        Webhook $webhook,
        string $status,
        ?int $reasonCode = null,
        ?string $message = null
    ) {
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

    /**
     * @inheritDoc
     */
    public function getOrderUrl(string $merchantReference): string
    {
        return  '';
    }
}
