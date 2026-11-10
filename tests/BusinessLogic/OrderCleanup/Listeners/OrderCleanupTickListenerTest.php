<?php

namespace SeQura\Core\Tests\BusinessLogic\OrderCleanup\Listeners;

use DateTime;
use Exception;
use RuntimeException;
use SeQura\Core\BusinessLogic\Domain\OrderCleanup\Services\OrderCleanupService;
use SeQura\Core\BusinessLogic\OrderCleanup\Listeners\OrderCleanupTickListener;
use SeQura\Core\BusinessLogic\OrderCleanup\Tasks\OrderCleanupTask;
use SeQura\Core\Infrastructure\Logger\Interfaces\ShopLoggerAdapter;
use SeQura\Core\Infrastructure\ORM\Interfaces\QueueItemRepository;
use SeQura\Core\Infrastructure\TaskExecution\Interfaces\Priority;
use SeQura\Core\Infrastructure\TaskExecution\QueueItem;
use SeQura\Core\Infrastructure\TaskExecution\QueueService;
use SeQura\Core\Infrastructure\Utility\TimeProvider;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\Logger\TestShopLogger;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\ORM\TestRepositoryRegistry;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\Utility\TestTimeProvider;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class OrderCleanupTickListenerTest
 *
 * @package SeQura\Core\Tests\BusinessLogic\OrderCleanup\Listeners
 */
class OrderCleanupTickListenerTest extends BaseTestCase
{
    /**
     * @var QueueItemRepository
     */
    private $queueRepository;

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
        $this->queueRepository = TestRepositoryRegistry::getQueueItemRepository();
        $this->cleanupService = TestServiceRegister::getService(OrderCleanupService::class);
    }

    /**
     * @throws Exception
     */
    public function testEnqueuesNothingWhenTheCleanupIsNotScheduled(): void
    {
        OrderCleanupTickListener::handle();

        self::assertEmpty($this->queueRepository->select());
    }

    /**
     * @throws Exception
     */
    public function testEnqueuesNothingBeforeTheNextRun(): void
    {
        $this->cleanupService->schedule($this->now + 60);

        OrderCleanupTickListener::handle();

        self::assertEmpty($this->queueRepository->select());
        self::assertSame($this->now + 60, $this->cleanupService->getNextRun());
    }

    /**
     * @throws Exception
     */
    public function testEnqueuesTheCleanupAndMovesTheNextRunADayAheadWhenDue(): void
    {
        $this->cleanupService->schedule($this->now - 60);

        OrderCleanupTickListener::handle();

        $queueItems = $this->queueRepository->select();
        self::assertCount(1, $queueItems);
        self::assertSame(OrderCleanupTask::getClassName(), $queueItems[0]->getTaskType());
        self::assertSame(OrderCleanupTickListener::QUEUE_NAME, $queueItems[0]->getQueueName());
        self::assertSame(Priority::LOW, $queueItems[0]->getPriority());
        self::assertSame($this->now - 60 + 86400, $this->cleanupService->getNextRun());
    }

    /**
     * @throws Exception
     */
    public function testSkipsTheRunWhileAnEarlierCleanupIsStillQueued(): void
    {
        $this->cleanupService->schedule($this->now - 60);
        $this->enqueueCleanup();

        OrderCleanupTickListener::handle();

        self::assertCount(1, $this->queueRepository->select());
        self::assertSame($this->now - 60 + 86400, $this->cleanupService->getNextRun());
    }

    /**
     * @throws Exception
     */
    public function testDoesNotRunASkippedCleanupOnceTheEarlierOneFinishes(): void
    {
        $this->cleanupService->schedule($this->now - 60);
        $earlier = $this->enqueueCleanup();
        OrderCleanupTickListener::handle();
        $earlier->setStatus(QueueItem::COMPLETED);
        $this->queueRepository->update($earlier);

        OrderCleanupTickListener::handle();

        self::assertCount(1, $this->queueRepository->select());
    }

    /**
     * @throws Exception
     */
    public function testEnqueuesOnceWhenTheSameDueRunTicksTwice(): void
    {
        $this->cleanupService->schedule($this->now - 60);

        OrderCleanupTickListener::handle();
        OrderCleanupTickListener::handle();

        self::assertCount(1, $this->queueRepository->select());
    }

    /**
     * @throws Exception
     */
    public function testEnqueuesAgainOnceTheEarlierCleanupHasFinished(): void
    {
        $this->cleanupService->schedule($this->now - 60);
        $earlier = $this->enqueueCleanup();
        $earlier->setStatus(QueueItem::COMPLETED);
        $this->queueRepository->update($earlier);

        OrderCleanupTickListener::handle();

        self::assertCount(2, $this->queueRepository->select());
    }

    /**
     * @throws Exception
     */
    public function testLogsAFailureInsteadOfLettingItLeaveTheTick(): void
    {
        $logger = new TestShopLogger();
        TestServiceRegister::registerService(ShopLoggerAdapter::CLASS_NAME, static function () use ($logger) {
            return $logger;
        });
        $failingService = $this->createMock(OrderCleanupService::class);
        $failingService->method('isDue')->willThrowException(new RuntimeException('storage unavailable'));
        TestServiceRegister::registerService(OrderCleanupService::class, static function () use ($failingService) {
            return $failingService;
        });

        OrderCleanupTickListener::handle();

        self::assertTrue($logger->isMessageContainedInLog('Order cleanup could not be enqueued.'));
        self::assertEmpty($this->queueRepository->select());
    }

    /**
     * @throws Exception
     */
    public function testKeepsTheRunDueWhenEnqueueingFails(): void
    {
        $this->cleanupService->schedule($this->now - 60);
        $failingQueue = $this->createMock(QueueService::class);
        $failingQueue->method('findLatestByType')->willReturn(null);
        $failingQueue->method('enqueue')->willThrowException(new RuntimeException('queue unavailable'));
        $this->registerQueueService($failingQueue);

        OrderCleanupTickListener::handle();

        self::assertSame($this->now - 60, $this->cleanupService->getNextRun());
    }

    /**
     * @throws Exception
     */
    public function testKeepsTheRunDueWhenCheckingForAPendingCleanupFails(): void
    {
        $this->cleanupService->schedule($this->now - 60);
        $failingQueue = $this->createMock(QueueService::class);
        $failingQueue->method('findLatestByType')->willThrowException(new RuntimeException('storage unavailable'));
        $failingQueue->expects(self::never())->method('enqueue');
        $this->registerQueueService($failingQueue);

        OrderCleanupTickListener::handle();

        self::assertSame($this->now - 60, $this->cleanupService->getNextRun());
    }

    /**
     * @throws Exception
     */
    public function testLogsTheEnqueueFailureWhenMovingTheRunBackFailsToo(): void
    {
        $logger = new TestShopLogger();
        TestServiceRegister::registerService(ShopLoggerAdapter::CLASS_NAME, static function () use ($logger) {
            return $logger;
        });
        $cleanupService = $this->createMock(OrderCleanupService::class);
        $cleanupService->method('isDue')->willReturn(true);
        $cleanupService->method('advanceNextRun')->willReturn($this->now - 60);
        $cleanupService->method('schedule')->willThrowException(new RuntimeException('storage unavailable'));
        TestServiceRegister::registerService(OrderCleanupService::class, static function () use ($cleanupService) {
            return $cleanupService;
        });
        $failingQueue = $this->createMock(QueueService::class);
        $failingQueue->method('findLatestByType')->willReturn(null);
        $failingQueue->method('enqueue')->willThrowException(new RuntimeException('queue unavailable'));
        $this->registerQueueService($failingQueue);

        OrderCleanupTickListener::handle();

        self::assertSame(
            [
                'Order cleanup could not move its next run back to the run that was due.' => 'storage unavailable',
                'Order cleanup could not be enqueued.' => 'queue unavailable',
            ],
            $this->loggedErrors($logger)
        );
    }

    /**
     * @param TestShopLogger $logger
     *
     * @return array<string, mixed>
     */
    private function loggedErrors(TestShopLogger $logger): array
    {
        $errors = [];
        foreach ($logger->loggedMessages as $logData) {
            $errors[$logData->getMessage()] = $logData->getContext()[0]->getValue();
        }

        return $errors;
    }

    /**
     * @param QueueService $queueService
     *
     * @return void
     */
    private function registerQueueService(QueueService $queueService): void
    {
        TestServiceRegister::registerService(QueueService::class, static function () use ($queueService) {
            return $queueService;
        });
    }

    /**
     * @return QueueItem
     *
     * @throws Exception
     */
    private function enqueueCleanup(): QueueItem
    {
        /**
         * @var QueueService $queueService
         */
        $queueService = TestServiceRegister::getService(QueueService::class);

        return $queueService->enqueue(OrderCleanupTickListener::QUEUE_NAME, new OrderCleanupTask());
    }
}
