<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\OrderCleanup\Services;

use DateTime;
use PHPUnit\Framework\MockObject\MockObject;
use RuntimeException;
use SeQura\Core\BusinessLogic\Domain\Integration\TaskExecution\TaskExecutorInterface;
use SeQura\Core\BusinessLogic\Domain\OrderCleanup\Services\OrderCleanupScheduler;
use SeQura\Core\BusinessLogic\Domain\OrderCleanup\Services\OrderCleanupService;
use SeQura\Core\BusinessLogic\Domain\OrderCleanup\Tasks\OrderCleanupTask;
use SeQura\Core\Infrastructure\Logger\Interfaces\ShopLoggerAdapter;
use SeQura\Core\Infrastructure\Logger\Logger;
use SeQura\Core\Infrastructure\Utility\TimeProvider;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\TaskExecution\RecordingTaskExecutor;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\Logger\TestShopLogger;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\Utility\TestTimeProvider;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;
use Throwable;
use TypeError;

class OrderCleanupSchedulerTest extends BaseTestCase
{
    /**
     * @var OrderCleanupService
     */
    private $cleanupService;

    /**
     * @var int
     */
    private $now;

    protected function setUp(): void
    {
        parent::setUp();

        $timeProvider = new TestTimeProvider();
        $timeProvider->setCurrentLocalTime(new DateTime('2026-10-30 03:00:00'));
        TestServiceRegister::registerService(TimeProvider::class, static function () use ($timeProvider) {
            return $timeProvider;
        });

        $this->now = $timeProvider->getCurrentLocalTime()->getTimestamp();
        $this->cleanupService = TestServiceRegister::getService(OrderCleanupService::class);
    }

    public function testHandsOverOneCleanupAndMovesTheNextRunADayAheadWhenDue(): void
    {
        $this->cleanupService->schedule($this->now - 60);
        $executor = new RecordingTaskExecutor();

        $this->scheduler($executor)->scheduleWhenDue();

        self::assertCount(1, $executor->enqueued);
        self::assertInstanceOf(OrderCleanupTask::class, $executor->enqueued[0]);
        self::assertSame([], $executor->delayed);
        self::assertSame($this->now - 60 + 86400, $this->cleanupService->getNextRun());
    }

    public function testHandsOverNothingWhenTheCleanupWasNeverScheduled(): void
    {
        $executor = new RecordingTaskExecutor();

        $this->scheduler($executor)->scheduleWhenDue();

        self::assertSame([], $executor->enqueued);
        self::assertSame([], $executor->delayed);
        self::assertNull($this->cleanupService->getNextRun());
    }

    public function testHandsOverNothingBeforeTheNextRun(): void
    {
        $this->cleanupService->schedule($this->now + 60);
        $executor = new RecordingTaskExecutor();

        $this->scheduler($executor)->scheduleWhenDue();

        self::assertSame([], $executor->enqueued);
        self::assertSame([], $executor->delayed);
        self::assertSame($this->now + 60, $this->cleanupService->getNextRun());
    }

    public function testHandsOverOnceWhenTheSameDueRunIsCheckedTwice(): void
    {
        $this->cleanupService->schedule($this->now - 60);
        $executor = new RecordingTaskExecutor();
        $scheduler = $this->scheduler($executor);

        $scheduler->scheduleWhenDue();
        $scheduler->scheduleWhenDue();

        self::assertCount(1, $executor->enqueued);
    }

    public function testKeepsTheRunDueAndRethrowsWhenTheHandOverFails(): void
    {
        $this->cleanupService->schedule($this->now - 60);
        $exception = new RuntimeException('queue unavailable');
        $executor = new RecordingTaskExecutor($exception);

        $this->assertRethrown($exception, $executor);

        self::assertSame($this->now - 60, $this->cleanupService->getNextRun());
    }

    public function testKeepsTheRunDueWhenTheHandOverFailsWithAnError(): void
    {
        $this->cleanupService->schedule($this->now - 60);
        $error = new TypeError('bad argument');
        $executor = new RecordingTaskExecutor($error);

        $this->assertRethrown($error, $executor);

        self::assertSame($this->now - 60, $this->cleanupService->getNextRun());
    }

    public function testLogsTheFailureToMoveTheRunBackAndStillRethrowsTheHandOverFailure(): void
    {
        $logger = new TestShopLogger();
        TestServiceRegister::registerService(ShopLoggerAdapter::CLASS_NAME, static function () use ($logger) {
            return $logger;
        });
        $cleanupService = $this->createMock(OrderCleanupService::class);
        $cleanupService->method('claimDueRun')->willReturn($this->now - 60);
        $cleanupService->method('schedule')->willThrowException(new RuntimeException('storage unavailable'));
        $exception = new RuntimeException('queue unavailable');

        try {
            (new OrderCleanupScheduler($cleanupService, new RecordingTaskExecutor($exception)))->scheduleWhenDue();
            self::fail('The hand-over failure should have been rethrown.');
        } catch (RuntimeException $thrown) {
            self::assertSame($exception, $thrown);
        }

        self::assertCount(1, $logger->loggedMessages);
        $logged = $logger->loggedMessages[0];
        self::assertSame(Logger::ERROR, $logged->getLogLevel());
        self::assertSame(
            'Order cleanup could not move its next run back to the run that was due.',
            $logged->getMessage()
        );
        self::assertSame('storage unavailable', $logged->getContext()[0]->getValue());
    }

    private function assertRethrown(Throwable $expected, TaskExecutorInterface $executor): void
    {
        try {
            $this->scheduler($executor)->scheduleWhenDue();
            self::fail('The hand-over failure should have been rethrown.');
        } catch (Throwable $thrown) {
            self::assertSame($expected, $thrown);
        }
    }

    private function scheduler(TaskExecutorInterface $executor): OrderCleanupScheduler
    {
        return new OrderCleanupScheduler($this->cleanupService, $executor);
    }
}
