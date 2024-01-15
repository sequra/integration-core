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
     * @param Webhook $webhook
     * @param string $status
     * @param int|null $reasonCode
     * @param string|null $message
     *
     * @return mixed
     */
    public function updateStatus(Webhook $webhook, string $status, ?int $reasonCode = null, ?string $message = null);

    /**
     * Provides ids of orders that should be included in the delivery report.
     *
     * @param int $page
     * @param int $limit
     *
     * @return string[] | int[]
     */
    public function getReportOrderIds(int $page, int $limit = 5000): array;

    /**
     * Provides ids of orders that should be included in the statistical report.
     *
     * @param int $page
     * @param int $limit
     *
     * @return string[] | int[]
     */
    public function getStatisticsOrderIds(int $page, int $limit = 5000): array;

    /**
     * @param string $merchantReference
     *
     * @return string
     */
    public function getOrderUrl(string $merchantReference): string;
}
