<?php

namespace SeQura\Core\BusinessLogic\Domain\OrderCleanup\Tasks;

use SeQura\Core\BusinessLogic\Domain\OrderCleanup\Services\OrderCleanupService;
use SeQura\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use SeQura\Core\Infrastructure\ServiceRegister;
use SeQura\Core\Infrastructure\TaskExecution\Task;

/**
 * Class OrderCleanupTask
 *
 * Runs the cleanup of stale solicitations. The solicitations are not store-scoped, so the task needs no context.
 *
 * @package SeQura\Core\BusinessLogic\Domain\OrderCleanup\Tasks
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
     * @inheritDoc
     *
     * @throws QueryFilterInvalidParamException
     */
    public function execute(): void
    {
        $this->getOrderCleanupService()->cleanup(function () {
            $this->reportAlive();
        });
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
