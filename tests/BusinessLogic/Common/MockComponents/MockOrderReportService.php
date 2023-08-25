<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use SeQura\Core\BusinessLogic\Domain\Integration\OrderReport\OrderReportServiceInterface;

/**
 * Class MockOrderReportService
 *
 * @package SeQura\Core\Tests\BusinessLogic\Common\MockComponents
 */
class MockOrderReportService implements OrderReportServiceInterface
{
    /**
     * @inheritDoc
     */
    public function getOrderReports(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getOrderStatistics(): array
    {
        return [];
    }
}
