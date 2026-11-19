<?php

namespace SeQura\Core\BusinessLogic\Domain\OrderCleanup\Tasks;

use Generator;
use SeQura\Core\BusinessLogic\Domain\Integration\TaskExecution\BusinessTaskInterface;
use SeQura\Core\BusinessLogic\Domain\OrderCleanup\Services\OrderCleanupService;
use SeQura\Core\Infrastructure\Logger\Logger;
use SeQura\Core\Infrastructure\ServiceRegister;

class OrderCleanupTask implements BusinessTaskInterface
{
    /**
     * @return Generator<int, int|null, mixed, void>
     */
    public function execute(): Generator
    {
        $deleted = yield from $this->getOrderCleanupService()->cleanup();
        Logger::logInfo(
            $deleted === null
                ? 'Order cleanup skipped: the order storage cannot delete by condition.'
                : "Order cleanup deleted up to $deleted stale solicitations."
        );

        yield 100;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [];
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return BusinessTaskInterface
     */
    public static function fromArray(array $data): BusinessTaskInterface
    {
        return new self();
    }

    /**
     * @return OrderCleanupService
     */
    protected function getOrderCleanupService(): OrderCleanupService
    {
        return ServiceRegister::getService(OrderCleanupService::class);
    }
}
