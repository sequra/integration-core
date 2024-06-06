<?php

/** @noinspection PhpDuplicateArrayKeysInspection */

namespace SeQura\Core\Tests\Infrastructure\TaskExecution;

use SeQura\Core\Infrastructure\Configuration\ConfigEntity;
use SeQura\Core\Infrastructure\Configuration\Configuration;
use SeQura\Core\Infrastructure\Configuration\ConfigurationManager;
use SeQura\Core\Infrastructure\Http\HttpClient;
use SeQura\Core\Infrastructure\Logger\Interfaces\DefaultLoggerAdapter;
use SeQura\Core\Infrastructure\Logger\Interfaces\ShopLoggerAdapter;
use SeQura\Core\Infrastructure\Logger\Logger;
use SeQura\Core\Infrastructure\ORM\RepositoryRegistry;
use SeQura\Core\Infrastructure\Serializer\Concrete\NativeSerializer;
use SeQura\Core\Infrastructure\Serializer\Serializer;
use SeQura\Core\Infrastructure\TaskExecution\AsyncProcessStarterService;
use SeQura\Core\Infrastructure\TaskExecution\Events\QueueItemStateTransitionEventBus;
use SeQura\Core\Infrastructure\TaskExecution\Interfaces\AsyncProcessService;
use SeQura\Core\Infrastructure\TaskExecution\Interfaces\TaskRunnerStatusStorage;
use SeQura\Core\Infrastructure\TaskExecution\Interfaces\TaskRunnerWakeup;
use SeQura\Core\Infrastructure\TaskExecution\Process;
use SeQura\Core\Infrastructure\TaskExecution\QueueItem;
use SeQura\Core\Infrastructure\TaskExecution\QueueItemStarter;
use SeQura\Core\Infrastructure\TaskExecution\QueueService;
use SeQura\Core\Infrastructure\TaskExecution\TaskRunner;
use SeQura\Core\Infrastructure\TaskExecution\TaskRunnerStatus;
use SeQura\Core\Infrastructure\Utility\Events\EventBus;
use SeQura\Core\Infrastructure\Utility\GuidProvider;
use SeQura\Core\Infrastructure\Utility\TimeProvider;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\Logger\TestDefaultLogger;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\Logger\TestShopLogger;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\ORM\MemoryQueueItemRepository;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\ORM\MemoryRepository;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\ORM\MemoryStorage;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution\FooTask;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution\InvalidTask;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution\TestQueueService;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution\TestRunnerStatusStorage;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution\TestTaskRunnerWakeupService;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TestConfigurationManager;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TestHttpClient;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TestShopConfiguration;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\Utility\TestGuidProvider;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\Utility\TestTimeProvider;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;
use DateTime;
use PHPUnit\Framework\TestCase;

/**
 * Class TaskRunnerTest
 *
 * @package SeQura\Core\Tests\Infrastructure\TaskExecution
 */
class TaskRunnerTest extends TestCase
{
    /** @var AsyncProcessService */
    private $asyncProcessStarter;
    /** @var \SeQura\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution\TestTaskRunnerWakeupService */
    private $taskRunnerStarter;
    /** @var TestRunnerStatusStorage */
    private $runnerStatusStorage;
    /** @var TestTimeProvider */
    private $timeProvider;
    /** @var TestGuidProvider */
    private $guidProvider;
    /** @var TestShopConfiguration */
    private $configuration;
    /** @var \SeQura\Core\Tests\Infrastructure\Common\TestComponents\Logger\TestShopLogger */
    private $logger;
    /** @var \SeQura\Core\Tests\Infrastructure\Common\TestComponents\ORM\MemoryQueueItemRepository */
    private $queueStorage;
    /** @var TestQueueService */
    private $queue;
    /** @var TaskRunner */
    private $taskRunner;

    /**
     * @throws \Exception
     */
    protected function setUp(): void
    {
        RepositoryRegistry::registerRepository(QueueItem::CLASS_NAME, MemoryQueueItemRepository::getClassName());
        RepositoryRegistry::registerRepository(ConfigEntity::CLASS_NAME, MemoryRepository::getClassName());
        RepositoryRegistry::registerRepository(Process::CLASS_NAME, MemoryRepository::getClassName());

        $taskRunnerStarter = new TestTaskRunnerWakeupService();
        $runnerStatusStorage = new TestRunnerStatusStorage();
        $timeProvider = new TestTimeProvider();
        $guidProvider = TestGuidProvider::getInstance();
        $configuration = new TestShopConfiguration();
        $queue = new TestQueueService();

        $shopLogger = new TestShopLogger();

        new TestServiceRegister(
            array(
                ConfigurationManager::CLASS_NAME => function () {
                    return new TestConfigurationManager();
                },
                AsyncProcessService::CLASS_NAME => function () {
                    return AsyncProcessStarterService::getInstance();
                },
                TaskRunnerWakeup::CLASS_NAME => function () use ($taskRunnerStarter) {
                    return $taskRunnerStarter;
                },
                TaskRunnerStatusStorage::CLASS_NAME => function () use ($runnerStatusStorage) {
                    return $runnerStatusStorage;
                },
                QueueService::CLASS_NAME => function () use ($queue) {
                    return $queue;
                },
                TimeProvider::CLASS_NAME => function () use ($timeProvider) {
                    return $timeProvider;
                },
                GuidProvider::CLASS_NAME => function () use ($guidProvider) {
                    return $guidProvider;
                },
                DefaultLoggerAdapter::CLASS_NAME => function () {
                    return new TestDefaultLogger();
                },
                ShopLoggerAdapter::CLASS_NAME => function () use ($shopLogger) {
                    return $shopLogger;
                },
                Configuration::CLASS_NAME => function () use ($configuration) {
                    return $configuration;
                },
                EventBus::CLASS_NAME => function () {
                    return EventBus::getInstance();
                },
                HttpClient::CLASS_NAME => function () {
                    return new TestHttpClient();
                },
                Serializer::CLASS_NAME => function () {
                    return new NativeSerializer();
                },
                QueueItemStateTransitionEventBus::CLASS_NAME => function () {
                    return QueueItemStateTransitionEventBus::getInstance();
                },
            )
        );

        Logger::resetInstance();

        $this->asyncProcessStarter = AsyncProcessStarterService::getInstance();
        $this->taskRunnerStarter = $taskRunnerStarter;
        $this->runnerStatusStorage = $runnerStatusStorage;
        $this->queueStorage = RepositoryRegistry::getQueueItemRepository();
        $this->timeProvider = $timeProvider;
        $this->guidProvider = $guidProvider;
        $this->configuration = $configuration;
        $this->logger = $shopLogger;
        $this->queue = $queue;
        $this->taskRunner = new TaskRunner();

        $guid = $this->guidProvider->generateGuid();
        $currentTimestamp = $this->timeProvider->getCurrentLocalTime()->getTimestamp();
        $this->taskRunner->setGuid($guid);
        $this->runnerStatusStorage->initializeStatus(new TaskRunnerStatus($guid, $currentTimestamp));
    }

    protected function tearDown(): void
    {
        MemoryStorage::reset();
        AsyncProcessStarterService::resetInstance();
        parent::tearDown();
    }

    /**
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     * @throws \Exception
     */
    public function testRunningQueuedItems()
    {
        // Arrange
        $this->timeProvider->setCurrentLocalTime(new DateTime('now -3 days'));
        $earliestQueue1Item = $this->queue->enqueue('queue1', new FooTask());
        $earliestQueue2Item = $this->queue->enqueue('queue2', new FooTask());

        $this->timeProvider->setCurrentLocalTime(new DateTime('now -2 days'));
        $this->queue->generateRunningQueueItem('queue3', new FooTask());
        $this->queue->enqueue('queue1', new FooTask());
        $this->queue->enqueue('queue2', new FooTask());
        $this->queue->enqueue('queue3', new FooTask());

        // Act
        $this->taskRunner->run();

        // Assert
        $startCallHistory = RepositoryRegistry::getRepository(Process::CLASS_NAME)->select();
        $this->assertCount(2, $startCallHistory, 'Run call should start earliest queued items asynchronously.');
        $this->assertTrue(
            $this->isQueueItemInStartCallHistory($earliestQueue1Item, $startCallHistory),
            'Run call should start only earliest queued items asynchronously.'
        );
        $this->assertTrue(
            $this->isQueueItemInStartCallHistory($earliestQueue2Item, $startCallHistory),
            'Run call should start only earliest queued items asynchronously.'
        );
    }

    /**
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     * @throws \Exception
     */
    public function testMaximumConcurrentExecutionLimit()
    {
        // Arrange
        $this->configuration->setMaxStartedTasksLimit(6);

        $this->timeProvider->setCurrentLocalTime(new DateTime('now -2 days'));
        $earliestQueue5Item = $this->queue->enqueue('queue5', new FooTask());
        $this->timeProvider->setCurrentLocalTime(new DateTime('now -3 days'));
        $earliestQueue4Item = $this->queue->enqueue('queue4', new FooTask());
        $this->timeProvider->setCurrentLocalTime(new DateTime('now -4 days'));
        $earliestQueue3Item = $this->queue->enqueue('queue3', new FooTask());
        $this->timeProvider->setCurrentLocalTime(new DateTime('now -5 days'));
        $earliestQueue2Item = $this->queue->enqueue('queue2', new FooTask());
        $this->timeProvider->setCurrentLocalTime(new DateTime('now -6 days'));
        $earliestQueue1Item = $this->queue->enqueue('queue1', new FooTask());

        $this->timeProvider->setCurrentLocalTime(new DateTime('now -7 days'));
        $this->queue->generateRunningQueueItem('runningQueue1', new FooTask());
        $this->queue->generateRunningQueueItem('runningQueue2', new FooTask());
        $this->queue->generateRunningQueueItem('runningQueue3', new FooTask());

        // Act
        $this->taskRunner->run();

        // Assert
        $startCallHistory = RepositoryRegistry::getRepository(Process::CLASS_NAME)->select();
        $this->assertCount(
            3,
            $startCallHistory,
            'Run call should start only up to max allowed running tasks where already running tasks must be considered.'
        );
        $this->assertTrue(
            $this->isQueueItemInStartCallHistory($earliestQueue1Item, $startCallHistory),
            'Run call should start only earliest queued items asynchronously.'
        );
        $this->assertTrue(
            $this->isQueueItemInStartCallHistory($earliestQueue2Item, $startCallHistory),
            'Run call should start only earliest queued items asynchronously.'
        );
        $this->assertTrue(
            $this->isQueueItemInStartCallHistory($earliestQueue3Item, $startCallHistory),
            'Run call should start only earliest queued items asynchronously.'
        );
        $this->assertFalse(
            $this->isQueueItemInStartCallHistory($earliestQueue4Item, $startCallHistory),
            'Run call should start only earliest queued items asynchronously.'
        );
        $this->assertFalse(
            $this->isQueueItemInStartCallHistory($earliestQueue5Item, $startCallHistory),
            'Run call should start only earliest queued items asynchronously.'
        );
    }

    /**
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     * @throws \SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     */
    public function testBatchTaskStarting()
    {
        // Arrange
        $this->configuration->setMaxStartedTasksLimit(8);
        $this->configuration->setAsyncStarterBatchSize(2);

        $this->timeProvider->setCurrentLocalTime(new DateTime('now -2 days'));
        $this->queue->enqueue('queue5', new FooTask());
        $this->timeProvider->setCurrentLocalTime(new DateTime('now -3 days'));
        $this->queue->enqueue('queue4', new FooTask());
        $this->timeProvider->setCurrentLocalTime(new DateTime('now -4 days'));
        $this->queue->enqueue('queue3', new FooTask());
        $this->timeProvider->setCurrentLocalTime(new DateTime('now -5 days'));
        $this->queue->enqueue('queue2', new FooTask());
        $this->timeProvider->setCurrentLocalTime(new DateTime('now -6 days'));
        $this->queue->enqueue('queue1', new FooTask());

        // Act
        $this->taskRunner->run();

        // Assert
        $processes = RepositoryRegistry::getRepository(Process::CLASS_NAME)->select();
        $this->assertCount(3, $processes, 'Run call should start all available items in a batch.');
        /** @var Process $process */
        $process = $processes[0];
        $this->assertInstanceOf('SeQura\Core\Infrastructure\TaskExecution\AsyncBatchStarter', $process->getRunner());
        /** @var Process $process */
        $process = $processes[1];
        $this->assertInstanceOf('SeQura\Core\Infrastructure\TaskExecution\AsyncBatchStarter', $process->getRunner());
        $process = $processes[2];
        $this->assertInstanceOf('SeQura\Core\Infrastructure\TaskExecution\QueueItemStarter', $process->getRunner());
    }

    /**
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     * @throws \Exception
     */
    public function testRequeueProgressedButExpiredTask()
    {
        // Arrange
        $this->timeProvider->setCurrentLocalTime(new DateTime('now -7 days'));
        $progress = 31;
        $lastExecutionProgress = 30;
        $expiredRunningItem = $this->queue->generateRunningQueueItem(
            'runningQueue1',
            new FooTask(),
            $progress,
            $lastExecutionProgress
        );
        $this->timeProvider->setCurrentLocalTime(new DateTime());

        // Act
        $this->taskRunner->run();

        // Assert
        $requeueCallHistory = $this->queue->getMethodCallHistory('requeue');
        $this->assertCount(
            1,
            $requeueCallHistory,
            'Run call should requeue expired tasks if it progressed since last execution.'
        );
        /** @var QueueItem $actualItem */
        $actualItem = $requeueCallHistory[0]['queueItem'];
        $this->assertEquals($expiredRunningItem->getId(), $actualItem->getId());
    }

    /**
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     * @throws \Exception
     */
    public function testFailingExpiredRunningTasks()
    {
        // Arrange
        $this->timeProvider->setCurrentLocalTime(new DateTime('now -7 days'));
        $expiredRunningItem = $this->queue->generateRunningQueueItem('runningQueue1', new FooTask(), 5269, 5269);
        $this->timeProvider->setCurrentLocalTime(new DateTime());

        // Act
        $this->taskRunner->run();

        // Assert
        $failCallHistory = $this->queue->getMethodCallHistory('fail');
        $this->assertCount(1, $failCallHistory, 'Run call should fail expired tasks.');

        /** @var QueueItem $actualItem */
        $actualItem = $failCallHistory[0]['queueItem'];
        /** @var \SeQura\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution\FooTask $actualTestTask */
        $actualTestTask = $actualItem->getTask();
        $actualFailureDescription = $failCallHistory[0]['failureDescription'];
        $this->assertEquals($expiredRunningItem->getId(), $actualItem->getId());
        $this->assertSame(
            1,
            $actualTestTask->getMethodCallCount('reconfigure'),
            'Run call should reconfigure failing expired tasks.'
        );
        $this->assertStringContainsString((string)$expiredRunningItem->getId(), $actualFailureDescription);
        $this->assertStringContainsString($expiredRunningItem->getTaskType(), $actualFailureDescription);
        $this->assertStringContainsString('failed due to extended inactivity period', $actualFailureDescription);
    }

    public function testFailingExpiredRunningTasksWhenTaskCantBeDeserialized()
    {
        // Arrange
        $this->timeProvider->setCurrentLocalTime(new DateTime('now -7 days'));
        $expiredRunningItem = $this->queue->generateRunningQueueItem('runningQueue1', new InvalidTask(), 5269, 5269);
        $this->timeProvider->setCurrentLocalTime(new DateTime());

        // Act
        $this->taskRunner->run();

        // Assert
        $failCallHistory = $this->queue->getMethodCallHistory('fail');
        $this->assertCount(1, $failCallHistory, 'Run call should fail expired tasks.');

        /** @var QueueItem $actualItem */
        $actualItem = $failCallHistory[0]['queueItem'];
        $actualFailureDescription = $failCallHistory[0]['failureDescription'];
        $actualForce = $failCallHistory[0]['force'];
        $this->assertEquals($expiredRunningItem->getId(), $actualItem->getId());
        $this->assertTrue($actualForce);
        $this->assertStringContainsString((string)$expiredRunningItem->getId(), $actualFailureDescription);
        $this->assertStringContainsString('Task deserialization failed', $actualFailureDescription);
    }

    public function testRunnerShouldBeInactiveAtTheEndOfLifecycle()
    {
        // Arrange
        $guid = 'test';
        $this->taskRunner->setGuid($guid);

        // Act
        $this->taskRunner->run();

        // Assert
        $setStatusCallHistory = $this->runnerStatusStorage->getMethodCallHistory('setStatus');
        $this->assertCount(
            1,
            $setStatusCallHistory,
            'Run call must set current runner as inactive at the end of lifecycle.'
        );

        /** @var TaskRunnerStatus $runnerStatus */
        $runnerStatus = $setStatusCallHistory[0]['status'];
        $this->assertEquals(
            TaskRunnerStatus::createNullStatus(),
            $runnerStatus,
            'Run call must set current runner as inactive at the end of lifecycle.'
        );
    }

    /**
     * @throws \Exception
     */
    public function testAutoWakeup()
    {
        // Arrange
        $startTime = new DateTime();
        $this->timeProvider->setCurrentLocalTime($startTime);

        // Act
        $this->taskRunner->run();

        // Assert
        $wakeupCallHistory = $this->taskRunnerStarter->getMethodCallHistory('wakeup');
        $this->assertCount(1, $wakeupCallHistory, 'Run call must auto wakeup at the end of lifecycle.');

        $expectedTimestamp = $startTime->getTimestamp() + TaskRunner::WAKEUP_DELAY;
        $actualTimestamp = $this->timeProvider->getCurrentLocalTime()->getTimestamp();
        $this->assertGreaterThanOrEqual($expectedTimestamp, $actualTimestamp, 'Wakeup call must be delayed.');
    }

    /**
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     * @throws \Exception
     */
    public function testRunWhenRunnerExpired()
    {
        // Arrange
        $this->timeProvider->setCurrentLocalTime(new DateTime('now -7 days'));
        $this->queue->generateRunningQueueItem('runningQueue1', new FooTask());

        $this->timeProvider->setCurrentLocalTime(new DateTime('now -2 days'));
        $currentTimestamp = $this->timeProvider->getCurrentLocalTime()->getTimestamp();
        $this->runnerStatusStorage->initializeStatus(new TaskRunnerStatus('test', $currentTimestamp));
        $this->taskRunner->setGuid('test');

        $this->timeProvider->setCurrentLocalTime(new DateTime());
        $this->queue->enqueue('queue', new FooTask());

        $this->taskRunnerStarter->resetCallHistory();

        // Act
        $this->taskRunner->run();

        // Assert
        $startCallHistory = RepositoryRegistry::getRepository(Process::CLASS_NAME)->select();
        $failCallHistory = $this->queue->getMethodCallHistory('fail');
        $wakeupCallHistory = $this->taskRunnerStarter->getMethodCallHistory('wakeup');
        $this->assertCount(1, $wakeupCallHistory, 'Run call must auto wakeup if no active runner is detected.');
        $this->assertCount(0, $startCallHistory, 'Run call when there is no live runner must not start any task.');
        $this->assertCount(0, $failCallHistory, 'Run call when there is no live runner must not fail any task.');
        $this->assertTrue(
            $this->logger->isMessageContainedInLog('Task runner started but it is expired.'),
            'Task runner must log messages when it detects expiration.'
        );
    }

    /**
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     * @throws \Exception
     */
    public function testRunWhenRunnerGuidIsNotSetAsLive()
    {
        // Arrange
        $this->timeProvider->setCurrentLocalTime(new DateTime('now -7 days'));
        $this->queue->generateRunningQueueItem('runningQueue1', new FooTask());

        $this->timeProvider->setCurrentLocalTime(new DateTime());
        $this->queue->enqueue('queue', new FooTask());

        $currentTimestamp = $this->timeProvider->getCurrentLocalTime()->getTimestamp();
        $this->runnerStatusStorage->initializeStatus(new TaskRunnerStatus('test', $currentTimestamp));
        $this->taskRunner->setGuid('different_guid');

        $this->taskRunnerStarter->resetCallHistory();

        // Act
        $this->taskRunner->run();

        // Assert
        $startCallHistory = RepositoryRegistry::getRepository(Process::CLASS_NAME)->select();
        $failCallHistory = $this->queue->getMethodCallHistory('fail');
        $wakeupCallHistory = $this->taskRunnerStarter->getMethodCallHistory('wakeup');
        $this->assertCount(1, $wakeupCallHistory, 'Run call must auto wakeup if no active runner is detected.');
        $this->assertCount(0, $startCallHistory, 'Run call when there is no live runner must not start any task.');
        $this->assertCount(0, $failCallHistory, 'Run call when there is no live runner must not fail any task.');
        $this->assertTrue(
            $this->logger->isMessageContainedInLog('Task runner started but it is not active anymore.'),
            'Task runner must log messages when it detects expiration.'
        );
    }

    /**
     * Checks wheter queue item is in call history.
     *
     * @param \SeQura\Core\Infrastructure\TaskExecution\QueueItem $needle
     * @param array $callHistory
     *
     * @return bool
     */
    private function isQueueItemInStartCallHistory(QueueItem $needle, array $callHistory)
    {
        /** @var QueueItem $queueItem */
        /** @var Process $callHistoryItem */
        foreach ($callHistory as $callHistoryItem) {
            /** @var QueueItemStarter $queueItemStarter */
            $queueItemStarter = $callHistoryItem->getRunner();
            if ($queueItemStarter->getQueueItemId() === $needle->getId()) {
                return true;
            }
        }

        return false;
    }
}
