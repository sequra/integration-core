<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\OrderCleanup\Tasks;

use Exception;
use SeQura\Core\BusinessLogic\Domain\OrderCleanup\Services\OrderCleanupService;
use SeQura\Core\BusinessLogic\Domain\OrderCleanup\Tasks\OrderCleanupTask;
use SeQura\Core\Infrastructure\Serializer\Serializer;
use SeQura\Core\Infrastructure\TaskExecution\Interfaces\Priority;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class OrderCleanupTaskTest
 *
 * @package SeQura\Core\Tests\BusinessLogic\Domain\OrderCleanup\Tasks
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

    public function testRunsWithLowPriority(): void
    {
        self::assertSame(Priority::LOW, (new OrderCleanupTask())->getPriority());
    }

    /**
     * @throws Exception
     */
    public function testRunsTheCleanup(): void
    {
        $cleanupService = $this->createMock(OrderCleanupService::class);
        $cleanupService->expects(self::once())->method('cleanup')->willReturn(0);
        TestServiceRegister::registerService(OrderCleanupService::class, static function () use ($cleanupService) {
            return $cleanupService;
        });

        (new OrderCleanupTask())->execute();
    }
}
