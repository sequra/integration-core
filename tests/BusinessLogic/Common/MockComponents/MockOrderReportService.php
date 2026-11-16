<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use SeQura\Core\BusinessLogic\Domain\Integration\OrderReport\OrderReportServiceInterface;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Platform;

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
    public function getOrderReports(array $orderIds): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getOrderStatistics(array $orderIds): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getPlatform(): Platform
    {
        return new Platform(
            'testName',
            'testVersion',
            'testUName',
            'testDbName',
            'testDbVersion',
            'testPluginVersion',
            'testPhpVersion'
        );
    }
}
