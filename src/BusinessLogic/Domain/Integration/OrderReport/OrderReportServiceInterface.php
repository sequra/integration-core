<?php

namespace SeQura\Core\BusinessLogic\Domain\Integration\OrderReport;

use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Platform;
use SeQura\Core\BusinessLogic\Domain\OrderReport\Models\OrderReport;
use SeQura\Core\BusinessLogic\Domain\OrderReport\Models\OrderStatistics;

/**
 * Interface OrderReportServiceInterface
 *
 * @package SeQura\Core\BusinessLogic\Domain\Integration\OrderReport
 */
interface OrderReportServiceInterface
{
    /**
     * Returns reports for all orders made by SeQura payment methods in the last 24 hours.
     *
     * @param string[] $orderIds
     *
     * @return OrderReport[]
     */
    public function getOrderReports(array $orderIds): array;

    /**
     * Returns statistics for all shop orders created in the last 7 days.
     *
     * @param string[] $orderIds
     *
     * @return OrderStatistics[]
     */
    public function getOrderStatistics(array $orderIds): array;

    /**
     * Returns the Platform instance.
     *
     * @return Platform
     */
    public function getPlatform(): Platform;
}
