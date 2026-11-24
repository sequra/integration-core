<?php

namespace SeQura\Core\BusinessLogic\Domain\OrderReport\Service;

use SeQura\Core\BusinessLogic\Domain\Integration\TaskExecution\TaskExecutorInterface;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\OrderReport\OrderReporter;
use SeQura\Core\BusinessLogic\Domain\StatisticalData\Services\StatisticalDataService;
use SeQura\Core\Infrastructure\Logger\LogContextData;
use SeQura\Core\Infrastructure\Logger\Logger;
use Throwable;

/**
 * @package SeQura\Core\BusinessLogic\Domain\OrderReport\Service
 */
class OrderReportScheduler
{
    /**
     * @var StatisticalDataService
     */
    private $statisticalDataService;

    /**
     * @var TaskExecutorInterface
     */
    private $taskExecutor;

    /**
     * @param StatisticalDataService $statisticalDataService
     * @param TaskExecutorInterface $taskExecutor
     */
    public function __construct(StatisticalDataService $statisticalDataService, TaskExecutorInterface $taskExecutor)
    {
        $this->statisticalDataService = $statisticalDataService;
        $this->taskExecutor = $taskExecutor;
    }

    /**
     * @return void
     */
    public function scheduleDue(): void
    {
        foreach ($this->statisticalDataService->getContextsForSendingReport() as $context) {
            try {
                StoreContext::doWithStore($context, function () {
                    $this->statisticalDataService->setSendReportTime();
                    $this->taskExecutor->enqueue(new OrderReporter());
                });
            } catch (Throwable $exception) {
                Logger::logError(
                    'Order report for store ' . $context . ' could not be scheduled.',
                    'Core',
                    [new LogContextData('storeId', $context), new LogContextData('error', $exception->getMessage())]
                );
            }
        }
    }
}
