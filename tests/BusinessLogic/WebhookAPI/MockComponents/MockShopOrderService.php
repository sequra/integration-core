<?php

namespace SeQura\Core\Tests\BusinessLogic\WebhookAPI\MockComponents;

use SeQura\Core\BusinessLogic\Domain\Webhook\Models\Webhook;
use SeQura\Core\BusinessLogic\Webhook\Services\ShopOrderService;

/**
 * Class MockShopOrderService
 *
 * @package SeQura\Core\Tests\BusinessLogic\WebhookAPI\MockComponents
 */
class MockShopOrderService implements ShopOrderService
{
    public $reportOrderIds = [];
    public $statisticsOrderIds = [];

    /**
     * @inheritDoc
     */
    public function updateStatus(
        Webhook $webhook,
        string $status,
        ?int $reasonCode = null,
        ?string $message = null
    ): void {
    }

    /**
     * @inheritDoc
     */
    public function getReportOrderIds(int $page, int $limit = 5000): array
    {
        return array_slice($this->reportOrderIds, $page * $limit, $limit);
    }

    /**
     * @inheritDoc
     */
    public function getStatisticsOrderIds(int $page, int $limit = 5000): array
    {
        return array_slice($this->statisticsOrderIds, $page * $limit, $limit);
    }

    /**
     * @inheritDoc
     */
    public function getOrderUrl(string $merchantReference): string
    {
        return 'https.test.url/' . $merchantReference;
    }
}
