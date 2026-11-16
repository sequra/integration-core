<?php

namespace SeQura\Core\BusinessLogic\Domain\OrderReport\Listeners;

use Exception;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\OrderReport\OrderReporter;
use SeQura\Core\BusinessLogic\Domain\StatisticalData\Services\StatisticalDataService;
use SeQura\Core\Infrastructure\Logger\LogContextData;
use SeQura\Core\Infrastructure\Logger\Logger;
use SeQura\Core\Infrastructure\ServiceRegister;
use SeQura\Core\Infrastructure\TaskExecution\QueueService;
use Throwable;

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
            try {
                StoreContext::doWithStore($context, static function () use ($context) {
                    static::getStatisticalDataService()->setSendReportTime();
                    static::getQueueService()->enqueue('order-reports-' . $context, new OrderReporter(), $context);
                });
            } catch (Throwable $exception) {
                Logger::logError(
                    'Order report for store ' . $context . ' could not be enqueued.',
                    'Core',
                    [new LogContextData('storeId', $context), new LogContextData('error', $exception->getMessage())]
                );
            }
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
