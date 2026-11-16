<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\TaskExecution\Services;

use DateTime;
use Exception;
use SeQura\Core\BusinessLogic\Domain\Integration\Store\StoreServiceInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\TaskExecution\RecurringWorkLockInterface;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\OrderCleanup\Services\OrderCleanupService;
use SeQura\Core\BusinessLogic\Domain\OrderReport\OrderReporter;
use SeQura\Core\BusinessLogic\Domain\SendReport\Models\SendReport;
use SeQura\Core\BusinessLogic\Domain\SendReport\RepositoryContracts\SendReportRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\StatisticalData\Services\StatisticalDataService;
use SeQura\Core\BusinessLogic\Domain\TaskExecution\Services\RecurringWorkRunner;
use SeQura\Core\Infrastructure\Logger\Interfaces\ShopLoggerAdapter;
use SeQura\Core\Infrastructure\Logger\LogData;
use SeQura\Core\Infrastructure\Logger\Logger;
use SeQura\Core\Infrastructure\ORM\Interfaces\QueueItemRepository;
use SeQura\Core\Infrastructure\TaskExecution\Interfaces\Priority;
use SeQura\Core\Infrastructure\TaskExecution\Task;
use SeQura\Core\Infrastructure\Utility\TimeProvider;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockStoreService;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\Logger\TestShopLogger;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\ORM\TestRepositoryRegistry;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution\TestQueueService;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\Utility\TestTimeProvider;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

class RecurringWorkRunnerTest extends BaseTestCase
{
    /**
     * @var QueueItemRepository
     */
    private $queueRepository;

    /**
     * @var SendReportRepositoryInterface
     */
    private $sendReportRepository;

    /**
     * @var OrderCleanupService
     */
    private $cleanupService;

    /**
     * @var TestTimeProvider
     */
    private $timeProvider;

    /**
     * @var TestShopLogger
     */
    private $logger;

    /**
     * @var int
     */
    private $now;

    protected function setUp(): void
    {
        parent::setUp();

        $this->logger = new TestShopLogger();
        TestServiceRegister::registerService(ShopLoggerAdapter::CLASS_NAME, function () {
            return $this->logger;
        });

        $this->timeProvider = new TestTimeProvider();
        $this->timeProvider->setCurrentLocalTime(new DateTime());
        TestServiceRegister::registerService(TimeProvider::class, function () {
            return $this->timeProvider;
        });
        TestServiceRegister::registerService(StoreServiceInterface::class, static function () {
            return new MockStoreService();
        });

        $this->now = $this->timeProvider->getCurrentLocalTime()->getTimestamp();
        $this->queueRepository = TestRepositoryRegistry::getQueueItemRepository();
        $this->sendReportRepository = TestServiceRegister::getService(SendReportRepositoryInterface::class);
        $this->cleanupService = TestServiceRegister::getService(OrderCleanupService::class);
    }

    public function testEnqueuesTheReportOfEveryDueStoreAndMovesItsTimeADayAhead(): void
    {
        $this->setSendReportTime('store-7', $this->now - 3600);
        $this->setSendReportTime('store-9', $this->now - 3600);
        $this->setSendReportTime('store-3', $this->now + 3600);
        $expected = $this->now + 86400;

        $this->runner($this->lock(true))->runDue();

        $items = $this->queueRepository->select();
        self::assertCount(2, $items);
        $queues = [];
        foreach ($items as $item) {
            self::assertSame(OrderReporter::getClassName(), $item->getTaskType());
            $queues[$item->getQueueName()] = $item->getContext();
        }
        self::assertSame(['order-reports-store-7' => 'store-7', 'order-reports-store-9' => 'store-9'], $queues);
        self::assertSame($expected, $this->getSendReportTime('store-7'));
        self::assertSame($expected, $this->getSendReportTime('store-9'));
        self::assertSame($this->now + 3600, $this->getSendReportTime('store-3'));
    }

    public function testSecondRunStartsNothingMore(): void
    {
        $this->setSendReportTime('store-7', $this->now - 3600);
        $this->cleanupService->schedule($this->now - 3600);
        $runner = $this->runner($this->lock(true));

        $runner->runDue();
        $runner->runDue();

        self::assertCount(2, $this->queueRepository->select());
    }

    public function testEnqueuesTheCleanupWhenDue(): void
    {
        $this->cleanupService->schedule($this->now - 3600);

        $this->runner($this->lock(true))->runDue();

        $items = $this->queueRepository->select();
        self::assertCount(1, $items);
        self::assertSame('OrderCleanupTask', $items[0]->getTaskType());
        self::assertSame('order-cleanup', $items[0]->getQueueName());
        self::assertSame('', $items[0]->getContext());
        self::assertSame(Priority::LOW, $items[0]->getPriority());
        self::assertGreaterThan($this->now, $this->cleanupService->getNextRun());
    }

    public function testStartsNothingWhenNothingIsDue(): void
    {
        $this->setSendReportTime('store-7', $this->now + 3600);
        $this->cleanupService->schedule($this->now + 3600);

        $this->runner($this->lock(true))->runDue();

        self::assertEmpty($this->queueRepository->select());
    }

    public function testDoesNothingWhenTheLockIsHeld(): void
    {
        $this->setSendReportTime('store-7', $this->now - 3600);
        $this->cleanupService->schedule($this->now - 3600);
        $lock = $this->lock(false);

        $this->runner($lock)->runDue();

        self::assertEmpty($this->queueRepository->select());
        self::assertSame($this->now - 3600, $this->getSendReportTime('store-7'));
        self::assertSame($this->now - 3600, $this->cleanupService->getNextRun());
        self::assertSame(0, $lock->releases);
    }

    public function testHoldsTheLockForTheWholeRun(): void
    {
        $this->setSendReportTime('store-7', $this->now - 3600);
        $lock = $this->lock(true);
        $lock->onRelease = function () use ($lock) {
            $lock->itemsAtRelease = count($this->queueRepository->select());
        };

        $this->runner($lock)->runDue();

        self::assertSame(1, $lock->acquisitions);
        self::assertSame(1, $lock->releases);
        self::assertSame(1, $lock->itemsAtRelease);
    }

    public function testStoreFailureDoesNotStopOtherStores(): void
    {
        $this->setSendReportTime('store-7', $this->now - 3600);
        $this->setSendReportTime('store-9', $this->now - 3600);
        TestServiceRegister::registerService(\SeQura\Core\Infrastructure\TaskExecution\QueueService::class, static function () {
            return new class extends TestQueueService {
                public function enqueue(
                    string $queueName,
                    Task $task,
                    string $context = '',
                    int $priority = Priority::NORMAL,
                    ?int $earliestStartTimestamp = null
                ): \SeQura\Core\Infrastructure\TaskExecution\QueueItem {
                    if ($queueName === 'order-reports-store-7') {
                        throw new Exception('queue down');
                    }

                    return parent::enqueue($queueName, $task, $context, $priority, $earliestStartTimestamp);
                }
            };
        });

        $this->runner($this->lock(true))->runDue();

        $items = $this->queueRepository->select();
        self::assertCount(1, $items);
        self::assertSame('order-reports-store-9', $items[0]->getQueueName());
        self::assertSame($this->now + 86400, $this->getSendReportTime('store-7'));
        $errors = $this->loggedAt(Logger::ERROR);
        self::assertCount(1, $errors);
        self::assertStringContainsString('store-7', $errors[0]->getMessage());
    }

    public function testReportFailureDoesNotStopTheCleanup(): void
    {
        $this->cleanupService->schedule($this->now - 3600);
        $statisticalData = $this->createMock(StatisticalDataService::class);
        $statisticalData->method('getContextsForSendingReport')->willThrowException(new Exception('db down'));
        TestServiceRegister::registerService(StatisticalDataService::class, static function () use ($statisticalData) {
            return $statisticalData;
        });

        $this->runner($this->lock(true))->runDue();

        $items = $this->queueRepository->select();
        self::assertCount(1, $items);
        self::assertSame('order-cleanup', $items[0]->getQueueName());
        self::assertNotEmpty($this->loggedAt(Logger::ERROR));
    }

    public function testCleanupFailureDoesNotStopTheReports(): void
    {
        $this->setSendReportTime('store-7', $this->now - 3600);
        $cleanupService = $this->createMock(OrderCleanupService::class);
        $cleanupService->method('claimDueRun')->willThrowException(new Exception('db down'));
        TestServiceRegister::registerService(OrderCleanupService::class, static function () use ($cleanupService) {
            return $cleanupService;
        });

        $this->runner($this->lock(true))->runDue();

        $items = $this->queueRepository->select();
        self::assertCount(1, $items);
        self::assertSame('order-reports-store-7', $items[0]->getQueueName());
        self::assertNotEmpty($this->loggedAt(Logger::ERROR));
    }

    private function runner(RecurringWorkLockInterface $lock): RecurringWorkRunner
    {
        return new RecurringWorkRunner($lock);
    }

    private function lock(bool $acquirable): RecordingLock
    {
        return new RecordingLock($acquirable);
    }

    private function setSendReportTime(string $storeId, int $timestamp): void
    {
        StoreContext::doWithStore($storeId, function () use ($timestamp) {
            $this->sendReportRepository->setSendReport(new SendReport($timestamp));
        });
    }

    private function getSendReportTime(string $storeId): int
    {
        return StoreContext::doWithStore($storeId, function () {
            return $this->sendReportRepository->getSendReport()->getSendReportTime();
        });
    }

    /**
     * @return LogData[]
     */
    private function loggedAt(int $level): array
    {
        return array_values(array_filter($this->logger->loggedMessages, static function (LogData $data) use ($level) {
            return $data->getLogLevel() === $level;
        }));
    }
}
