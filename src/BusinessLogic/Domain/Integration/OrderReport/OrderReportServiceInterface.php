<?php

namespace SeQura\Core\BusinessLogic\Domain\Integration\OrderReport;

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
     * @return OrderReport[]
     */
    public function getOrderReports(): array;

    /**
     * Returns statistics for all shop orders created in the last 7 days.
     *
     * @return OrderStatistics[]
     */
    public function getOrderStatistics(): array;
}
