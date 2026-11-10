<?php

namespace SeQura\Core\BusinessLogic\OrderCleanup\Tasks;

use SeQura\Core\BusinessLogic\Domain\OrderCleanup\Services\OrderCleanupService;
use SeQura\Core\Infrastructure\Logger\Logger;
use SeQura\Core\Infrastructure\ServiceRegister;
use SeQura\Core\Infrastructure\TaskExecution\Task;

/**
 * Class OrderCleanupTask
 *
 * Runs the cleanup of stale solicitations. The solicitations are not store-scoped, so the task needs no context.
 *
 * @package SeQura\Core\BusinessLogic\OrderCleanup\Tasks
 */
class OrderCleanupTask extends Task
{
    /**
     * @inheritDoc
     */
    public function __serialize(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function __unserialize(array $data): void
    {
    }

    /**
     * @return void
     */
    public function execute(): void
    {
        $cleanupService = $this->getOrderCleanupService();
        if (!$cleanupService->canCleanup()) {
            Logger::logInfo('Order cleanup skipped: the order storage cannot delete by condition.');
            $this->reportProgress(100);

            return;
        }

        $deleted = $cleanupService->cleanup(function () {
            $this->reportAlive();
        });
        Logger::logInfo("Order cleanup deleted up to $deleted stale solicitations.");
        $this->reportProgress(100);
    }

    /**
     * @return OrderCleanupService
     */
    protected function getOrderCleanupService(): OrderCleanupService
    {
        return ServiceRegister::getService(OrderCleanupService::class);
    }
}
