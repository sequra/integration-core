<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\OrderCleanup\Services;

use DateTime;
use RuntimeException;
use SeQura\Core\BusinessLogic\BootstrapComponent;
use SeQura\Core\BusinessLogic\Domain\Integration\StoreIntegration\StoreIntegrationServiceInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\TaskExecution\RecurringWorkLockInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\TaskExecution\TaskExecutorInterface;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\OrderCleanup\Services\OrderCleanupScheduler;
use SeQura\Core\BusinessLogic\Domain\OrderCleanup\Services\OrderCleanupService;
use SeQura\Core\BusinessLogic\Domain\OrderCleanup\Tasks\OrderCleanupTask;
use SeQura\Core\BusinessLogic\Domain\OrderReport\Service\OrderReportScheduler;
use SeQura\Core\BusinessLogic\Domain\TaskExecution\Services\RecurringWorkRunner;
use SeQura\Core\BusinessLogic\TaskExecution\Standalone\TaskAdapter;
use SeQura\Core\BusinessLogic\TaskExecution\Standalone\TaskRunnerExecutor;
use SeQura\Core\Infrastructure\Logger\Interfaces\ShopLoggerAdapter;
use SeQura\Core\Infrastructure\Logger\Logger;
use SeQura\Core\Infrastructure\ORM\Interfaces\QueueItemRepository;
use SeQura\Core\Infrastructure\ServiceRegister;
use SeQura\Core\Infrastructure\TaskExecution\Interfaces\Priority;
use SeQura\Core\Infrastructure\TaskExecution\QueueItem;
use SeQura\Core\Infrastructure\TaskExecution\QueueService;
use SeQura\Core\Infrastructure\Utility\Events\EventBus;
use SeQura\Core\Infrastructure\Utility\TimeProvider;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockIntegrationStoreIntegrationService;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\TaskExecution\RecordingTaskExecutor;
use SeQura\Core\Tests\BusinessLogic\Domain\TaskExecution\Services\RecordingLock;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\Logger\TestShopLogger;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\ORM\TestRepositoryRegistry;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution\TestQueueService;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution\TestTaskRunnerWakeupService;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\Utility\TestTimeProvider;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;
use SeQura\Core\Infrastructure\TaskExecution\Interfaces\TaskRunnerWakeup;

class OrderCleanupSchedulerBootstrapTest extends BaseTestCase
{
    /**
     * @var int
     */
    private $now;

    /**
     * @var QueueItemRepository
     */
    private $queueRepository;

    protected function setUp(): void
    {
        parent::setUp();

        EventBus::resetInstance();
        BootstrapComponent::init();

        $timeProvider = new TestTimeProvider();
        $timeProvider->setCurrentLocalTime(new DateTime());
        $this->now = $timeProvider->getCurrentLocalTime()->getTimestamp();
        TestServiceRegister::registerService(TimeProvider::class, static function () use ($timeProvider) {
            return $timeProvider;
        });
        TestServiceRegister::registerService(QueueService::class, static function () {
            return new TestQueueService();
        });
        TestServiceRegister::registerService(TaskRunnerWakeup::CLASS_NAME, static function () {
            return new TestTaskRunnerWakeupService();
        });

        TestServiceRegister::registerService(
            StoreIntegrationServiceInterface::class,
            static function () {
                return new MockIntegrationStoreIntegrationService();
            }
        );

        $this->queueRepository = TestRepositoryRegistry::getQueueItemRepository();
    }

    public function testIsRegisteredByTheBootstrapAndTheOldClassesAreGone(): void
    {
        self::assertInstanceOf(
            OrderCleanupScheduler::class,
            ServiceRegister::getService(OrderCleanupScheduler::class)
        );
        self::assertInstanceOf(TaskRunnerExecutor::class, ServiceRegister::getService(TaskExecutorInterface::class));
        self::assertFalse(class_exists('SeQura\Core\BusinessLogic\OrderCleanup\Tasks\OrderCleanupTask'));
        self::assertFalse(class_exists('SeQura\Core\BusinessLogic\OrderCleanup\Listeners\OrderCleanupTickListener'));
    }

    public function testQueuesTheCleanupOnTheBuiltInRunnerAtLowPriorityOutsideAnyStore(): void
    {
        $this->makeCleanupDue();

        ServiceRegister::getService(OrderCleanupScheduler::class)->scheduleWhenDue();

        $items = $this->queueRepository->select();
        self::assertCount(1, $items);
        self::assertSame('order-cleanup', $items[0]->getQueueName());
        self::assertSame(Priority::LOW, $items[0]->getPriority());
        self::assertSame('', $items[0]->getContext());
        self::assertSame('OrderCleanupTask', $items[0]->getTaskType());
        self::assertNotNull($items[0]->getTask());
        self::assertSame('OrderCleanupTask', $items[0]->getTask()->getType());
    }

    public function testQueuesTheCleanupWithoutAContextWhateverTheCurrentStore(): void
    {
        $this->makeCleanupDue();

        StoreContext::doWithStore('store-7', static function () {
            ServiceRegister::getService(OrderCleanupScheduler::class)->scheduleWhenDue();
        });

        $items = $this->queueRepository->select();
        self::assertCount(1, $items);
        self::assertSame('', $items[0]->getContext());
    }

    public function testAStillQueuedCleanupDoesNotStopTheNextDueRun(): void
    {
        $this->makeCleanupDue();
        $queued = new QueueItem(new TaskAdapter(new OrderCleanupTask(), ''));
        $queued->setQueueName('order-cleanup');
        $queued->setStatus(QueueItem::QUEUED);
        $this->queueRepository->save($queued);

        ServiceRegister::getService(OrderCleanupScheduler::class)->scheduleWhenDue();

        self::assertCount(2, $this->queueRepository->select());
    }

    public function testRunDueHandsOneCleanupToTheRegisteredScheduler(): void
    {
        $this->makeCleanupDue();
        $executor = new RecordingTaskExecutor();
        $this->registerExecutor($executor);
        $this->registerLock();

        ServiceRegister::getService(RecurringWorkRunner::class)->runDue();

        self::assertCount(1, $executor->enqueued);
        self::assertInstanceOf(OrderCleanupTask::class, $executor->enqueued[0]);
    }

    public function testRunDueLogsAFailedHandOverAndKeepsTheRunDue(): void
    {
        $this->makeCleanupDue();
        $logger = new TestShopLogger();
        TestServiceRegister::registerService(ShopLoggerAdapter::CLASS_NAME, static function () use ($logger) {
            return $logger;
        });
        $this->registerExecutor(new RecordingTaskExecutor(new RuntimeException('queue unavailable')));
        $this->registerLock();

        ServiceRegister::getService(RecurringWorkRunner::class)->runDue();

        self::assertCount(1, $logger->loggedMessages);
        self::assertSame(Logger::ERROR, $logger->loggedMessages[0]->getLogLevel());
        self::assertSame($this->now - 60, ServiceRegister::getService(OrderCleanupService::class)->getNextRun());
    }

    private function makeCleanupDue(): void
    {
        ServiceRegister::getService(OrderCleanupService::class)->schedule($this->now - 60);
    }

    private function registerExecutor(TaskExecutorInterface $executor): void
    {
        TestServiceRegister::registerService(TaskExecutorInterface::class, static function () use ($executor) {
            return $executor;
        });
    }

    private function registerLock(): void
    {
        TestServiceRegister::registerService(RecurringWorkLockInterface::class, static function () {
            return new RecordingLock(true);
        });
        TestServiceRegister::registerService(RecurringWorkRunner::class, static function () {
            return new RecurringWorkRunner(
                new RecordingLock(true),
                ServiceRegister::getService(OrderReportScheduler::class)
            );
        });
    }
}
