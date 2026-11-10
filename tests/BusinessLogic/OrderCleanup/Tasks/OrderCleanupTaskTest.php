<?php

namespace SeQura\Core\Tests\BusinessLogic\OrderCleanup\Tasks;

use Exception;
use SeQura\Core\BusinessLogic\Domain\OrderCleanup\Services\OrderCleanupService;
use SeQura\Core\BusinessLogic\OrderCleanup\Tasks\OrderCleanupTask;
use SeQura\Core\Infrastructure\Logger\Interfaces\ShopLoggerAdapter;
use SeQura\Core\Infrastructure\Serializer\Serializer;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\Logger\TestShopLogger;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class OrderCleanupTaskTest
 *
 * @package SeQura\Core\Tests\BusinessLogic\OrderCleanup\Tasks
 */
class OrderCleanupTaskTest extends BaseTestCase
{
    public function testSurvivesASerializationRoundTrip(): void
    {
        $task = new OrderCleanupTask();

        $unserialized = Serializer::unserialize(Serializer::serialize($task));

        self::assertInstanceOf(OrderCleanupTask::class, $unserialized);
        self::assertInstanceOf(OrderCleanupTask::class, OrderCleanupTask::fromArray($task->toArray()));
    }

    /**
     * @throws Exception
     */
    public function testRunsTheCleanup(): void
    {
        $cleanupService = $this->createMock(OrderCleanupService::class);
        $cleanupService->method('canCleanup')->willReturn(true);
        $cleanupService->expects(self::once())->method('cleanup')->with(self::isType('callable'))->willReturn(0);
        TestServiceRegister::registerService(OrderCleanupService::class, static function () use ($cleanupService) {
            return $cleanupService;
        });

        (new OrderCleanupTask())->execute();
    }

    /**
     * @throws Exception
     */
    public function testLogsTheNumberOfDeletedSolicitations(): void
    {
        $logger = new TestShopLogger();
        TestServiceRegister::registerService(ShopLoggerAdapter::CLASS_NAME, static function () use ($logger) {
            return $logger;
        });
        $cleanupService = $this->createMock(OrderCleanupService::class);
        $cleanupService->method('canCleanup')->willReturn(true);
        $cleanupService->method('cleanup')->willReturn(3);
        TestServiceRegister::registerService(OrderCleanupService::class, static function () use ($cleanupService) {
            return $cleanupService;
        });

        (new OrderCleanupTask())->execute();

        self::assertTrue($logger->isMessageContainedInLog('Order cleanup deleted up to 3 stale solicitations.'));
    }

    /**
     * @throws Exception
     */
    public function testLogsASkippedCleanupWhenTheOrderStorageCannotDeleteByCondition(): void
    {
        $logger = new TestShopLogger();
        TestServiceRegister::registerService(ShopLoggerAdapter::CLASS_NAME, static function () use ($logger) {
            return $logger;
        });
        $cleanupService = $this->createMock(OrderCleanupService::class);
        $cleanupService->method('canCleanup')->willReturn(false);
        $cleanupService->expects(self::never())->method('cleanup');
        TestServiceRegister::registerService(OrderCleanupService::class, static function () use ($cleanupService) {
            return $cleanupService;
        });

        (new OrderCleanupTask())->execute();

        self::assertTrue(
            $logger->isMessageContainedInLog('Order cleanup skipped: the order storage cannot delete by condition.')
        );
    }
}
