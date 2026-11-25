<?php

namespace SeQura\Core\BusinessLogic\Domain\OrderReport\Listeners;

use Exception;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\OrderReport\OrderReporter;
use SeQura\Core\BusinessLogic\Domain\StatisticalData\Services\StatisticalDataService;
use SeQura\Core\Infrastructure\ServiceRegister;
use SeQura\Core\Infrastructure\TaskExecution\QueueService;

/**
 * Class TickEventListener
 *
 * @package SeQura\Core\BusinessLogic\Domain\OrderReport\Listeners
 */
class TickEventListener
{
    /**
     * @return void
     *
     * @throws Exception
     */
    public static function handle(): void
    {
        $contexts = static::getStatisticalDataService()->getContextsForSendingReport();

        foreach ($contexts as $context) {
            StoreContext::doWithStore($context, static function () use ($context) {
                static::getStatisticalDataService()->setSendReportTime();
                static::getQueueService()->enqueue('order-reports-' . $context, new OrderReporter(), $context);
            });
        }
    }

    /**
     * @return StatisticalDataService
     */
    protected static function getStatisticalDataService(): StatisticalDataService
    {
        return ServiceRegister::getService(StatisticalDataService::class);
    }

    /**
     * @return QueueService
     */
    protected static function getQueueService(): QueueService
    {
        return ServiceRegister::getService(QueueService::class);
    }
}
