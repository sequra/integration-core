<?php

namespace SeQura\Core\BusinessLogic\Domain\OrderReport\Listeners;

use Exception;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\OrderReport\OrderReporter;
use SeQura\Core\BusinessLogic\Domain\Stores\Services\StoreService;
use SeQura\Core\Infrastructure\ServiceRegister;
use SeQura\Core\Infrastructure\TaskExecution\QueueService;
use SeQura\Core\Infrastructure\Utility\TimeProvider;

/**
 * Class TickEventListener
 *
 * @package SeQura\Core\BusinessLogic\Domain\OrderReport\Listeners
 */
class TickEventListener
{
    private const SCHEDULE_TIME = '4 am';

    /**
     * @return void
     *
     * @throws Exception
     */
    public static function handle(): void
    {
        if (static::getTimeProvider()->getCurrentLocalTime()->getTimestamp()
            !== strtotime(self::SCHEDULE_TIME)) {
            return;
        }

        $connectedStores = static::getStoreService()->getConnectedStores();

        foreach ($connectedStores as $store) {
            StoreContext::doWithStore($store, static function () {
                static::getQueueService()->enqueue('order-reports', new OrderReporter());
            });
        }
    }

    /**
     * @return StoreService
     */
    private static function getStoreService(): StoreService
    {
        return ServiceRegister::getService(StoreService::class);
    }

    /**
     * @return QueueService
     */
    private static function getQueueService(): QueueService
    {
        return ServiceRegister::getService(QueueService::class);
    }

    /**
     * @return TimeProvider
     */
    private static function getTimeProvider(): TimeProvider
    {
        return ServiceRegister::getService(TimeProvider::class);
    }
}
