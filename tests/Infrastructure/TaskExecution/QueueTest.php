<?php

/** @noinspection PhpDuplicateArrayKeysInspection */

namespace SeQura\Core\Tests\Infrastructure\TaskExecution;

use SeQura\Core\Infrastructure\Configuration\ConfigEntity;
use SeQura\Core\Infrastructure\Configuration\Configuration;
use SeQura\Core\Infrastructure\Configuration\ConfigurationManager;
use SeQura\Core\Infrastructure\ORM\QueryFilter\QueryFilter;
use SeQura\Core\Infrastructure\ORM\RepositoryRegistry;
use SeQura\Core\Infrastructure\Serializer\Concrete\NativeSerializer;
use SeQura\Core\Infrastructure\Serializer\Serializer;
use SeQura\Core\Infrastructure\TaskExecution\Events\QueueItemStateTransitionEventBus;
use SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException;
use SeQura\Core\Infrastructure\TaskExecution\Interfaces\Priority;
use SeQura\Core\Infrastructure\TaskExecution\Interfaces\TaskRunnerWakeup;
use SeQura\Core\Infrastructure\TaskExecution\QueueItem;
use SeQura\Core\Infrastructure\TaskExecution\QueueItemStarter;
use SeQura\Core\Infrastructure\TaskExecution\QueueService;
use SeQura\Core\Infrastructure\TaskExecution\Task;
use SeQura\Core\Infrastructure\Utility\Events\EventBus;
use SeQura\Core\Infrastructure\Utility\TimeProvider;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\ORM\MemoryQueueItemRepository;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\ORM\MemoryRepository;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\ORM\MemoryStorage;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution\AbortTask;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution\BarTask;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution\FooOrchestrator;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution\FooTask;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution\TestQueueService;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution\TestTaskRunnerWakeupService;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TestConfigurationManager;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TestShopConfiguration;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\Utility\TestTimeProvider;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;
use DateTime;
use PHPUnit\Framework\TestCase;

/**
 * Class QueueTest
 *
 * @package SeQura\Core\Tests\Infrastructure\TaskExecution
 */
class QueueTest extends TestCase
{
    /** @var QueueService */
    public $queue;
    /** @var \SeQura\Core\Tests\Infrastructure\Common\TestComponents\ORM\MemoryQueueItemRepository */
    public $queueStorage;
    /** @var TestTimeProvider */
    public $timeProvider;
    /** @var \SeQura\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution\TestTaskRunnerWakeupService */
    public $taskRunnerStarter;

    /**
     * @throws \Exception
     */
    public function setUp(): void
    {
        parent::setUp();

        RepositoryRegistry::registerRepository(QueueItem::CLASS_NAME, MemoryQueueItemRepository::getClassName());
        RepositoryRegistry::registerRepository(ConfigEntity::CLASS_NAME, MemoryRepository::getClassName());

        $timeProvider = new TestTimeProvider();
        $taskRunnerStarter = new TestTaskRunnerWakeupService();
        $this->queue = new QueueService();
        $me = $this;

        new TestServiceRegister(
            array(
                ConfigurationManager::CLASS_NAME => function () {
                    return new TestConfigurationManager();
                },
                TimeProvider::CLASS_NAME => function () use ($timeProvider) {
                    return $timeProvider;
                },
                TaskRunnerWakeup::CLASS_NAME => function () use ($taskRunnerStarter) {
                    return $taskRunnerStarter;
                },
                Configuration::CLASS_NAME => function () {
                    return new TestShopConfiguration();
                },
                EventBus::CLASS_NAME => function () {
                    return EventBus::getInstance();
                },
                Serializer::CLASS_NAME => function () {
                    return new NativeSerializer();
                },
                QueueService::CLASS_NAME => function () use ($me) {
                    return $me->queue;
                },
                QueueItemStateTransitionEventBus::CLASS_NAME => function () {
                    return QueueItemStateTransitionEventBus::getInstance();
                },
            )
        );

        $this->queueStorage = RepositoryRegistry::getQueueItemRepository();
        $this->timeProvider = $timeProvider;
        $this->taskRunnerStarter = $taskRunnerStarter;
        $this->queue = new QueueService();
        MemoryStorage::reset();
    }

    /**
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     */
    public function testItShouldBePossibleToFindQueueItemById()
    {
        $queueItem = $this->queue->enqueue(
            'testQueue',
            new FooTask()
        );

        $foundQueueItem = $this->queue->find($queueItem->getId());

        $this->assertEquals(
            $queueItem->getId(),
            $foundQueueItem->getId(),
            'Finding queue item by id must return queue item with given id.'
        );
        $this->assertEquals(
            $queueItem->getStatus(),
            $foundQueueItem->getStatus(),
            'Finding queue item by id must return queue item with given id.'
        );
        $this->assertEquals(
            $queueItem->getQueueName(),
            $foundQueueItem->getQueueName(),
            'Finding queue item by id must return queue item with given id.'
        );
        $this->assertEquals(
            $queueItem->getLastExecutionProgressBasePoints(),
            $foundQueueItem->getLastExecutionProgressBasePoints(),
            'Finding queue item by id must return queue item with given id.'
        );
        $this->assertEquals(
            $queueItem->getProgressBasePoints(),
            $foundQueueItem->getProgressBasePoints(),
            'Finding queue item by id must return queue item with given id.'
        );
        $this->assertEquals(
            $queueItem->getRetries(),
            $foundQueueItem->getRetries(),
            'Finding queue item by id must return queue item with given id.'
        );
        $this->assertEquals(
            $queueItem->getFailureDescription(),
            $foundQueueItem->getFailureDescription(),
            'Finding queue item by id must return queue item with given id.'
        );
        $this->assertEquals(
            $queueItem->getCreateTimestamp(),
            $foundQueueItem->getCreateTimestamp(),
            'Finding queue item by id must return queue item with given id.'
        );
        $this->assertEquals(
            $queueItem->getQueueTimestamp(),
            $foundQueueItem->getQueueTimestamp(),
            'Finding queue item by id must return queue item with given id.'
        );
        $this->assertEquals(
            $queueItem->getLastUpdateTimestamp(),
            $foundQueueItem->getLastUpdateTimestamp(),
            'Finding queue item by id must return queue item with given id.'
        );
        $this->assertEquals(
            $queueItem->getStartTimestamp(),
            $foundQueueItem->getStartTimestamp(),
            'Finding queue item by id must return queue item with given id.'
        );
        $this->assertEquals(
            $queueItem->getFinishTimestamp(),
            $foundQueueItem->getFinishTimestamp(),
            'Finding queue item by id must return queue item with given id.'
        );
        $this->assertEquals(
            $queueItem->getFailTimestamp(),
            $foundQueueItem->getFailTimestamp(),
            'Finding queue item by id must return queue item with given id.'
        );
        $this->assertEquals(
            $queueItem->getEarliestStartTimestamp(),
            $foundQueueItem->getEarliestStartTimestamp(),
            'Finding queue item by id must return queue item with given id.'
        );
    }

    /**
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\AbortTaskExecutionException
     */
    public function testItShouldBePossibleToFindRunningQueueItems()
    {
        // Arrange
        $runningItem1 = $this->generateRunningQueueItem('testQueue', new FooTask());
        $runningItem2 = $this->generateRunningQueueItem(
            'testQueue',
            new FooTask()
        );
        $runningItem3 = $this->generateRunningQueueItem(
            'otherQueue',
            new FooTask()
        );
        $this->queue->enqueue(
            'testQueue',
            new FooTask()
        );
        $this->queue->enqueue(
            'otherQueue',
            new FooTask()
        );
        $this->queue->enqueue(
            'withoutRunningItemsQueue',
            new FooTask()
        );
        $queue = new QueueService();

        // Act
        $result = $queue->findRunningItems();

        // Assert
        $this->assertCount(3, $result);
        $this->assertTrue(
            $this->inArrayQueueItem($runningItem1, $result),
            'Find running queue items should contain all running queue items in queue.'
        );
        $this->assertTrue(
            $this->inArrayQueueItem($runningItem2, $result),
            'Find running queue items should contain all running queue items in queue.'
        );
        $this->assertTrue(
            $this->inArrayQueueItem($runningItem3, $result),
            'Find running queue items should contain all running queue items in queue.'
        );
    }

    /**
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     * @throws \Exception
     */
    public function testFindOldestQueuedItems()
    {
        // Arrange
        $this->timeProvider->setCurrentLocalTime(new DateTime('now -3 days'));
        $earliestQueue1Item = $this->queue->enqueue(
            'queue1',
            new FooTask()
        );
        $earliestQueue2Item = $this->queue->enqueue(
            'queue2',
            new FooTask()
        );

        $this->generateRunningQueueItem(
            'queue3',
            new FooTask()
        );

        $this->timeProvider->setCurrentLocalTime(new DateTime('now -2 days'));
        $this->queue->enqueue(
            'queue1',
            new FooTask()
        );
        $this->queue->enqueue(
            'queue2',
            new FooTask()
        );
        $this->queue->enqueue(
            'queue3',
            new FooTask()
        );

        // Act
        $result = $this->queue->findOldestQueuedItems();

        // Assert
        $this->assertCount(
            2,
            $result,
            'Find earliest queued items should contain only earliest queued items from all queues.'
        );
        $this->assertTrue(
            $this->inArrayQueueItem($earliestQueue1Item, $result),
            'Find earliest queued items should contain only earliest queued items from all queues.'
        );
        $this->assertTrue(
            $this->inArrayQueueItem($earliestQueue2Item, $result),
            'Find earliest queued items should contain only earliest queued items from all queues.'
        );
    }

    /**
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     * @throws \Exception
     */
    public function testFindLatestByType()
    {
        // Arrange
        $this->timeProvider->setCurrentLocalTime(new DateTime('now -3 days'));
        $this->queue->enqueue(
            'queue1',
            new FooTask(),
            'context'
        );
        $this->queue->enqueue('queue2', new FooTask(), 'context');

        $this->timeProvider->setCurrentLocalTime(new DateTime('now -2 days'));
        $latestQueueItem = $this->queue->enqueue(
            'queue1',
            new FooTask(),
            'context'
        );

        $this->timeProvider->setCurrentLocalTime(new DateTime('now -1 days'));
        $this->queue->enqueue('queue1', new BarTask(), 'context');
        $globallyLatestQueueItem = $this->queue->enqueue(
            'queue1',
            new FooTask(),
            'different context'
        );

        // Act
        $result = $this->queue->findLatestByType('FooTask', 'context');
        $globalResult = $this->queue->findLatestByType('FooTask');

        // Assert
        $this->assertNotNull(
            $result,
            'Find latest by type should contain latest queued item from all queues with given type in given context.'
        );
        $this->assertNotNull(
            $globalResult,
            'Find latest by type should contain latest queued item from all queues with given type.'
        );
        $this->assertSame(
            $latestQueueItem->getId(),
            $result->getId(),
            'Find latest by type should return latest queued item with given type from all queues in given context.'
        );
        $this->assertSame(
            $globallyLatestQueueItem->getId(),
            $globalResult->getId(),
            'Find latest by type should return latest queued item with given type from all queues.'
        );
    }

    /**
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     * @throws \Exception
     */
    public function testLimitOfFinOldestQueuedItems()
    {
        // Arrange
        $this->timeProvider->setCurrentLocalTime(new DateTime('now -2 days'));
        $this->queue->enqueue(
            'queue5',
            new FooTask()
        );
        $this->timeProvider->setCurrentLocalTime(new DateTime('now -3 days'));
        $this->queue->enqueue(
            'queue4',
            new FooTask()
        );
        $this->timeProvider->setCurrentLocalTime(new DateTime('now -4 days'));
        $earliestQueue3Item = $this->queue->enqueue(
            'queue3',
            new FooTask()
        );
        $this->timeProvider->setCurrentLocalTime(new DateTime('now -5 days'));
        $earliestQueue2Item = $this->queue->enqueue('queue2', new FooTask());
        $this->timeProvider->setCurrentLocalTime(new DateTime('now -6 days'));
        $earliestQueue1Item = $this->queue->enqueue(
            'queue1',
            new FooTask()
        );
        $queue = new QueueService();

        // Act
        $result = $queue->findOldestQueuedItems(3);

        // Assert
        $this->assertCount(3, $result, 'Find earliest queued items should be limited.');
        $this->assertTrue(
            $this->inArrayQueueItem($earliestQueue1Item, $result),
            'Find earliest queued items should contain only earliest queued items from all queues.'
        );
        $this->assertTrue(
            $this->inArrayQueueItem($earliestQueue2Item, $result),
            'Find earliest queued items should contain only earliest queued items from all queues.'
        );
        $this->assertTrue(
            $this->inArrayQueueItem($earliestQueue3Item, $result),
            'Find earliest queued items should contain only earliest queued items from all queues.'
        );
    }

    /**
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     * @throws \Exception
     */
    public function testItShouldBePossibleEnqueueTaskToQueue()
    {
        // Arrange
        $currentTime = new DateTime();
        $this->timeProvider->setCurrentLocalTime($currentTime);

        // Act
        $queueItem = $this->queue->enqueue(
            'testQueue',
            new FooTask()
        );

        // Assert
        $this->assertEquals(
            QueueItem::QUEUED,
            $queueItem->getStatus(),
            'When queued queue item must set status to "queued".'
        );
        $this->assertNotNull($queueItem->getId(), 'When queued queue item should be in storage. Id must not be null.');
        $this->assertArrayHasKey(
            $queueItem->getId(),
            MemoryStorage::$storage,
            'When queued queue item should be in storage.'
        );
        $this->assertEquals(
            'testQueue',
            $queueItem->getQueueName(),
            'When queued queue item should be in storage under given queue name.'
        );
        $this->assertSame(
            0,
            $queueItem->getLastExecutionProgressBasePoints(),
            'When queued queue item should NOT change last execution progress.'
        );
        $this->assertSame(0, $queueItem->getProgressBasePoints(), 'When queued queue item should NOT change progress.');
        $this->assertSame(0, $queueItem->getRetries(), 'When queued queue item must NOT change retries.');
        $this->assertSame(
            '',
            $queueItem->getFailureDescription(),
            'When queued queue item must NOT change failure description.'
        );
        $this->assertSame(
            $currentTime->getTimestamp(),
            $queueItem->getCreateTimestamp(),
            'When queued queue item must set create time.'
        );
        $this->assertSame(
            $currentTime->getTimestamp(),
            $queueItem->getQueueTimestamp(),
            'When queued queue item must record queue time.'
        );
        $this->assertNull($queueItem->getStartTimestamp(), 'When queued queue item must NOT change start time.');
        $this->assertNull($queueItem->getFinishTimestamp(), 'When queued queue item must NOT change finish time.');
        $this->assertNull($queueItem->getFailTimestamp(), 'When queued queue item must NOT change fail time.');
        $this->assertNull(
            $queueItem->getEarliestStartTimestamp(),
            'When queued queue item must NOT change earliest start time.'
        );
        $this->assertQueueItemIsSaved($queueItem);
    }

    /**
     * @throws \SeQura\Core\Infrastructure\ORM\Exceptions\EntityClassException
     * @throws \SeQura\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException
     */
    public function testItShouldBePossibleToEnqueueTaskInSpecificContext()
    {
        // Arrange
        $queueItem = $this->queue->enqueue(
            'testQueue',
            new FooTask(),
            'test'
        );
        $this->assertSame(
            'test',
            $queueItem->getContext(),
            'When queued in specific context queue item context must match provided context.'
        );
        $this->assertQueueItemIsSaved($queueItem);
    }

    /**
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     */
    public function testTaskEnqueueShouldWakeupTaskRunner()
    {
        // Act
        $this->queue->enqueue(
            'testQueue',
            new FooTask()
        );

        // Assert
        $wakeupCallHistory = $this->taskRunnerStarter->getMethodCallHistory('wakeup');
        $this->assertCount(1, $wakeupCallHistory, 'Task enqueue must wakeup task runner.');
    }

    /**
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     * @throws \Exception
     */
    public function testItShouldBePossibleToTransitToInProgressStateFromQueued()
    {
        // Arrange
        $task = new FooTask();

        $queuedTime = new DateTime('now -2 days');
        $this->timeProvider->setCurrentLocalTime($queuedTime);
        $queueItem = $this->queue->enqueue('testQueue', $task);

        $startTime = new DateTime('now -1 day');
        $this->timeProvider->setCurrentLocalTime($startTime);

        // Act
        $this->queue->start($queueItem);

        // Assert
        $this->assertSame(
            1,
            $task->getMethodCallCount('execute'),
            'When started queue item must call task execute method.'
        );
        $this->assertEquals(
            QueueItem::IN_PROGRESS,
            $queueItem->getStatus(),
            'When started queue item must set status to "in_progress".'
        );
        $this->assertSame(0, $queueItem->getRetries(), 'When started queue item must NOT change retries.');
        $this->assertSame(
            '',
            $queueItem->getFailureDescription(),
            'When started queue item must NOT change failure message.'
        );
        $this->assertSame(
            $queuedTime->getTimestamp(),
            $queueItem->getQueueTimestamp(),
            'When started queue item must NOT change queue time.'
        );
        $this->assertSame(
            $startTime->getTimestamp(),
            $queueItem->getStartTimestamp(),
            'When started queue item must record start time.'
        );
        $this->assertSame(
            $startTime->getTimestamp(),
            $queueItem->getLastUpdateTimestamp(),
            'When started queue item must set last update time.'
        );
        $this->assertNull($queueItem->getFinishTimestamp(), 'When started queue item must NOT finish time.');
        $this->assertNull($queueItem->getFailTimestamp(), 'When started queue item must NOT change fail time.');
        $this->assertNull(
            $queueItem->getEarliestStartTimestamp(),
            'When started queue item must NOT change earliest start time.'
        );
        $this->assertQueueItemIsSaved($queueItem);
    }

    /**
     * @throws \SeQura\Core\Infrastructure\ORM\Exceptions\EntityClassException
     * @throws \SeQura\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\AbortTaskExecutionException
     */
    public function testWhenInProgressReportedProgressShouldBeStoredUsingQueue()
    {
        // Arrange
        $task = new FooTask();
        $queueItem = $this->queue->enqueue('testQueue', $task);
        $this->queue->start($queueItem);

        // Act
        $task->reportProgress(10.12);

        // Assert
        $this->assertSame(
            1012,
            $queueItem->getProgressBasePoints(),
            'When started queue item must update task progress.'
        );
        $this->assertQueueItemIsSaved($queueItem);
    }

    /**
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\AbortTaskExecutionException
     */
    public function testWhenNotInProgressReportedProgressShouldFailJob()
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Progress reported for not started queue item.');

        // Arrange
        $task = new FooTask();
        $queueItem = $this->queue->enqueue('testQueue', $task);
        $this->queue->start($queueItem);
        $this->queue->fail($queueItem, 'Test failure description');

        // Act
        $task->reportProgress(25.78);

        // Assert
        $this->fail('Reporting progress on not started queue item should fail.');
    }

    /**
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     * @throws \Exception
     */
    public function testWhenInProgressReportedAliveShouldBeStoredWithCurrentTimeAsLastUpdatedTimestamp()
    {
        // Arrange
        $task = new FooTask();
        $queueItem = $this->queue->enqueue('testQueue', $task);
        $this->queue->start($queueItem);

        $lastSaveTime = new DateTime();
        $this->timeProvider->setCurrentLocalTime($lastSaveTime);

        // Act
        $task->reportAlive();

        // Assert
        $this->assertSame(
            $lastSaveTime->getTimestamp(),
            $queueItem->getLastUpdateTimestamp(),
            'When task alive reported queue item must be stored.'
        );
        $this->assertQueueItemIsSaved($queueItem);
    }

    /**
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     * @throws \Exception
     */
    public function testItShouldBePossibleToTransitToCompletedStateFromInProgress()
    {
        // Arrange
        $task = new FooTask();

        $queuedTime = new DateTime('now -3 days');
        $this->timeProvider->setCurrentLocalTime($queuedTime);
        $queueItem = $this->queue->enqueue('testQueue', $task);

        $startTime = new DateTime('now -2 days');
        $this->timeProvider->setCurrentLocalTime($startTime);
        $this->queue->start($queueItem);

        $finishTime = new DateTime('now -1 day');
        $this->timeProvider->setCurrentLocalTime($finishTime);

        // Act
        $this->queue->finish($queueItem);

        // Assert
        $this->assertEquals(
            QueueItem::COMPLETED,
            $queueItem->getStatus(),
            'When finished queue item must set status to "completed".'
        );
        $this->assertSame(0, $queueItem->getRetries(), 'When finished queue item must NOT change retries.');
        $this->assertSame(
            10000,
            $queueItem->getProgressBasePoints(),
            'When finished queue item must ensure 100% progress value.'
        );
        $this->assertSame(
            '',
            $queueItem->getFailureDescription(),
            'When finished queue item must NOT change failure message.'
        );
        $this->assertSame(
            $queuedTime->getTimestamp(),
            $queueItem->getQueueTimestamp(),
            'When finished queue item must NOT change queue time.'
        );
        $this->assertSame(
            $startTime->getTimestamp(),
            $queueItem->getStartTimestamp(),
            'When finished queue item must NOT change start time.'
        );
        $this->assertSame(
            $finishTime->getTimestamp(),
            $queueItem->getFinishTimestamp(),
            'When finished queue item must record finish time.'
        );
        $this->assertNull($queueItem->getFailTimestamp(), 'When finished queue item must NOT change fail time.');
        $this->assertNull(
            $queueItem->getEarliestStartTimestamp(),
            'When finished queue item must NOT change earliest start time.'
        );
        $this->assertQueueItemIsSaved($queueItem);
    }

    /**
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     * @throws \Exception
     */
    public function testRequeueStartedTaskShouldReturnQueueItemInQueuedState()
    {
        // Arrange
        $task = new FooTask();

        $queuedTime = new DateTime('now -3 days');
        $this->timeProvider->setCurrentLocalTime($queuedTime);
        $queueItem = $this->queue->enqueue('testQueue', $task);

        $startTime = new DateTime('now -2 days');
        $this->timeProvider->setCurrentLocalTime($startTime);
        $this->queue->start($queueItem);

        $queueItem->setProgressBasePoints(3081);

        // Act
        $this->queue->requeue($queueItem);

        // Assert
        $this->assertEquals(
            QueueItem::QUEUED,
            $queueItem->getStatus(),
            'When requeue queue item must set status to "queued".'
        );
        $this->assertSame(0, $queueItem->getRetries(), 'When requeue queue item must not change retries count.');
        $this->assertSame(
            3081,
            $queueItem->getLastExecutionProgressBasePoints(),
            'When requeue queue item must set last execution progress to current queue item progress value.'
        );
        $this->assertSame(
            $queuedTime->getTimestamp(),
            $queueItem->getQueueTimestamp(),
            'When requeue queue item must NOT change queue time.'
        );
        $this->assertNull($queueItem->getStartTimestamp(), 'When requeue queue item must reset start time.');
        $this->assertNull($queueItem->getFinishTimestamp(), 'When requeue queue item must NOT change finish time.');
        $this->assertNull($queueItem->getFailTimestamp(), 'When requeue queue item must NOT change fail time.');
        $this->assertQueueItemIsSaved($queueItem);
    }

    /**
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     * @throws \Exception
     */
    public function testFailingLessThanMaxRetryTimesShouldReturnQueueItemInQueuedState()
    {
        // Arrange
        $task = new FooTask();

        $queuedTime = new DateTime('now -3 days');
        $this->timeProvider->setCurrentLocalTime($queuedTime);
        $queueItem = $this->queue->enqueue('testQueue', $task);

        $startTime = new DateTime('now -2 days');
        $this->timeProvider->setCurrentLocalTime($startTime);
        $queueItem->setLastExecutionProgressBasePoints(3123);
        $this->queue->start($queueItem);

        $failTime = new DateTime('now -1 day');
        $this->timeProvider->setCurrentLocalTime($failTime);

        // Act
        for ($i = 0; $i < QueueService::MAX_RETRIES; $i++) {
            $this->queue->fail($queueItem, 'Test failure description');
            if ($i < QueueService::MAX_RETRIES - 1) {
                $this->queue->start($queueItem);
            }
        }

        // Assert
        $this->assertEquals(
            QueueItem::QUEUED,
            $queueItem->getStatus(),
            'When failed less than max retry times queue item must set status to "queued".'
        );
        $this->assertSame(
            5,
            $queueItem->getRetries(),
            'When failed queue item must increase retries by one up to max retries count.'
        );
        $this->assertSame(
            3123,
            $queueItem->getLastExecutionProgressBasePoints(),
            'When failed queue item must NOT reset last execution progress value.'
        );
        $this->assertStringStartsWith(
            'Attempt 1: Test failure description',
            $queueItem->getFailureDescription(),
            'When failed queue item must set failure description.'
        );
        $this->assertSame(
            $queuedTime->getTimestamp(),
            $queueItem->getQueueTimestamp(),
            'When failed queue item must NOT change queue time.'
        );
        $this->assertNull($queueItem->getStartTimestamp(), 'When failed queue item must reset start time.');
        $this->assertNull($queueItem->getFinishTimestamp(), 'When failed queue item NOT change finish time.');
        $this->assertNull(
            $queueItem->getFailTimestamp(),
            'When failed less than max retry times queue item must NOT change fail time.'
        );
        $this->assertQueueItemIsSaved($queueItem);
    }

    /**
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     * @throws \Exception
     */
    public function testFailingMoreThanMaxRetryTimesShouldTransitQueueItemInFailedState()
    {
        // Arrange
        $task = new FooTask();

        $queuedTime = new DateTime('now -3 days');
        $this->timeProvider->setCurrentLocalTime($queuedTime);
        $queueItem = $this->queue->enqueue('testQueue', $task);

        $this->timeProvider->setCurrentLocalTime(new DateTime('now -2 days'));
        $this->queue->start($queueItem);

        $failTime = new DateTime('now -1 day');
        $this->timeProvider->setCurrentLocalTime($failTime);

        // Act
        for ($i = 0; $i <= QueueService::MAX_RETRIES; $i++) {
            $this->queue->fail($queueItem, 'Test failure description');
            if ($i < QueueService::MAX_RETRIES) {
                $this->queue->start($queueItem);
            }
        }

        // Assert
        $this->assertEquals(
            QueueItem::FAILED,
            $queueItem->getStatus(),
            'When failed more than max retry times queue item must set status to "failed".'
        );
        $this->assertSame(
            6,
            $queueItem->getRetries(),
            'When failed queue item must increase retries by one up to max retries count.'
        );
        $this->assertStringStartsWith(
            'Attempt 1: Test failure description',
            $queueItem->getFailureDescription(),
            'When failed queue item must set failure description.'
        );
        $this->assertSame(
            $queuedTime->getTimestamp(),
            $queueItem->getQueueTimestamp(),
            'When failed queue item must NOT change queue time.'
        );
        $this->assertNull($queueItem->getFinishTimestamp(), 'When failed queue item NOT change finish time.');
        $this->assertSame(
            $failTime->getTimestamp(),
            $queueItem->getFailTimestamp(),
            'When failed more than max retry times queue item must set fail time.'
        );
        $this->assertQueueItemIsSaved($queueItem);
    }

    /**
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     * @throws \Exception
     */
    public function testFailMessages()
    {
        $task = new FooTask();

        $queueItem = $this->queue->enqueue('testQueue', $task);
        $this->queue->start($queueItem);

        for ($i = 0; $i <= QueueService::MAX_RETRIES; $i++) {
            $this->queue->fail($queueItem, 'Test' . $i);
            if ($i < QueueService::MAX_RETRIES) {
                $this->queue->start($queueItem);
            }
        }

        $this->assertEquals(
            "Attempt 1: Test0\nAttempt 2: Test1\nAttempt 3: Test2\nAttempt 4: Test3\nAttempt 5: Test4\nAttempt 6: Test5",
            $queueItem->getFailureDescription(),
            'Failure descriptions must be stacked.'
        );
    }

    /**
     * Test regular task abort.
     *
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\AbortTaskExecutionException
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     */
    public function testAbortQueueItemMethod()
    {
        $queueItem = $this->queue->enqueue('testQueue', new FooTask());
        $this->queue->start($queueItem);
        $this->queue->abort($queueItem, 'Abort message.');

        $this->assertEquals(
            QueueItem::ABORTED,
            $queueItem->getStatus(),
            'The status for an aborted task must be set to "aborted".'
        );

        $this->assertNotEmpty($queueItem->getFailureDescription(), 'Abort message is missing.');
        $this->assertNotEmpty($queueItem->getFailTimestamp(), 'Fail timestamp should be set when aborting a task.');
    }

    /**
     * Test regular task abort.
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     */
    public function testAbortingQueueItemFromTask()
    {
        $queueItem = $this->queue->enqueue('testQueue', new AbortTask());
        $itemStarter = new QueueItemStarter($queueItem->getId());
        $itemStarter->run();

        $queueItem = $this->queue->find($queueItem->getId());

        $this->assertEquals(
            QueueItem::ABORTED,
            $queueItem->getStatus(),
            'The status for an aborted task must be set to "aborted".'
        );

        $this->assertEquals('Attempt 1: Abort mission!', $queueItem->getFailureDescription(), 'Wrong abort message.');
        $this->assertNotEmpty($queueItem->getFailTimestamp(), 'Fail timestamp should be set when aborting a task.');
    }

    /**
     * Test regular task abort.
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\AbortTaskExecutionException
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     */
    public function testAbortingQueueItemAfterFailure()
    {
        $queueItem = $this->queue->enqueue('testQueue', new FooTask());
        $this->queue->start($queueItem);
        $this->queue->fail($queueItem, 'Fail message.');
        $this->queue->start($queueItem);
        $this->queue->abort($queueItem, 'Abort message.');

        $this->assertEquals(
            QueueItem::ABORTED,
            $queueItem->getStatus(),
            'The status for an aborted task must be set to "aborted".'
        );

        $this->assertEquals(
            "Attempt 1: Fail message.\nAttempt 2: Abort message.",
            $queueItem->getFailureDescription(),
            'Abort message should be appended to the failure message.'
        );
    }

    /**
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\AbortTaskExecutionException
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     */
    public function testStartingQueueItemAfterAbortion()
    {
        $this->expectException(\BadMethodCallException::class);

        $queueItem = $this->queue->enqueue('testQueue', new FooTask());
        $this->queue->start($queueItem);
        $this->queue->abort($queueItem, 'Abort message.');
        $this->queue->start($queueItem);

        $this->fail('Queue item should not be started after abortion.');
    }

    /**
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\AbortTaskExecutionException
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     */
    public function testItShouldBeForbiddenToTransitionFromCreatedToInProgressStatus()
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Illegal queue item state transition from "created" to "in_progress"');

        $queueItem = new QueueItem(new FooTask());

        $this->queue->start($queueItem);

        $this->fail('Queue item status transition from "created" to "in_progress" should not be allowed.');
    }

    /**
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     */
    public function testItShouldBeForbiddenToTransitionFromCreatedToFailedStatus()
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Illegal queue item state transition from "created" to "failed"');

        $queueItem = new QueueItem(new FooTask());

        $this->queue->fail($queueItem, 'Test failure description');

        $this->fail('Queue item status transition from "created" to "failed" should not be allowed.');
    }

    /**
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     */
    public function testItShouldBeForbiddenToTransitionFromCreatedToCompletedStatus()
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Illegal queue item state transition from "created" to "completed"');

        $queueItem = new QueueItem(new FooTask());

        $this->queue->finish($queueItem);

        $this->fail('Queue item status transition from "created" to "completed" should not be allowed.');
    }

    /**
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     */
    public function testItShouldBeForbiddenToTransitionFromQueuedToFailedStatus()
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Illegal queue item state transition from "queued" to "failed"');

        $queueItem = $this->queue->enqueue(
            'testQueue',
            new FooTask()
        );

        $this->queue->fail($queueItem, 'Test failure description');

        $this->fail('Queue item status transition from "queued" to "failed" should not be allowed.');
    }

    /**
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     */
    public function testItShouldBeForbiddenToTransitionFromQueuedToCompletedStatus()
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Illegal queue item state transition from "queued" to "completed"');

        $queueItem = $this->queue->enqueue(
            'testQueue',
            new FooTask()
        );

        $this->queue->finish($queueItem);

        $this->fail('Queue item status transition from "queued" to "completed" should not be allowed.');
    }

    /**
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\AbortTaskExecutionException
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     */
    public function testItShouldBeForbiddenToTransitionFromInProgressToInProgressStatus()
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Illegal queue item state transition from "in_progress" to "in_progress"');

        $queueItem = $this->queue->enqueue(
            'testQueue',
            new FooTask()
        );
        $this->queue->start($queueItem);

        $this->queue->start($queueItem);

        $this->fail('Queue item status transition from "in_progress" to "in_progress" should not be allowed.');
    }

    /**
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\AbortTaskExecutionException
     */
    public function testItShouldBeForbiddenToTransitionFromFailedToInProgressStatus()
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Illegal queue item state transition from "failed" to "in_progress"');

        // Arrange
        $queueItem = $this->queue->enqueue(
            'testQueue',
            new FooTask()
        );
        $this->queue->start($queueItem);
        for ($i = 0; $i <= QueueService::MAX_RETRIES; $i++) {
            $this->queue->fail($queueItem, 'Test failure description');
            if ($i < QueueService::MAX_RETRIES) {
                $this->queue->start($queueItem);
            }
        }

        // Act
        $this->queue->start($queueItem);

        $this->fail('Queue item status transition from "failed" to "in_progress" should not be allowed.');
    }

    /**
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\AbortTaskExecutionException
     */
    public function testItShouldBeForbiddenToTransitionFromFailedFailedStatus()
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Illegal queue item state transition from "failed" to "failed"');

        // Arrange
        $queueItem = $this->queue->enqueue(
            'testQueue',
            new FooTask()
        );
        $this->queue->start($queueItem);
        for ($i = 0; $i <= QueueService::MAX_RETRIES; $i++) {
            $this->queue->fail($queueItem, 'Test failure description');
            if ($i < QueueService::MAX_RETRIES) {
                $this->queue->start($queueItem);
            }
        }

        // Act
        $this->queue->fail($queueItem, 'Test failure description');

        $this->fail('Queue item status transition from "failed" to "failed" should not be allowed.');
    }

    /**
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\AbortTaskExecutionException
     */
    public function testItShouldBeForbiddenToTransitionFromFailedCompletedStatus()
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Illegal queue item state transition from "failed" to "completed"');

        // Arrange
        $queueItem = $this->queue->enqueue(
            'testQueue',
            new FooTask()
        );
        $this->queue->start($queueItem);
        for ($i = 0; $i <= QueueService::MAX_RETRIES; $i++) {
            $this->queue->fail($queueItem, 'Test failure description');
            if ($i < QueueService::MAX_RETRIES) {
                $this->queue->start($queueItem);
            }
        }

        // Act
        $this->queue->finish($queueItem);

        $this->fail('Queue item status transition from "failed" to "completed" should not be allowed.');
    }

    /**
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\AbortTaskExecutionException
     */
    public function testItShouldBeForbiddenToTransitionFromCompletedToInProgressStatus()
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Illegal queue item state transition from "completed" to "in_progress"');

        // Arrange
        $queueItem = $this->queue->enqueue(
            'testQueue',
            new FooTask()
        );
        $this->queue->start($queueItem);
        $this->queue->finish($queueItem);

        // Act
        $this->queue->start($queueItem);

        $this->fail('Queue item status transition from "completed" to "in_progress" should not be allowed.');
    }

    /**
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\AbortTaskExecutionException
     */
    public function testItShouldBeForbiddenToTransitionFromCompletedToFailedStatus()
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Illegal queue item state transition from "completed" to "failed"');

        // Arrange
        $queueItem = $this->queue->enqueue(
            'testQueue',
            new FooTask()
        );
        $this->queue->start($queueItem);
        $this->queue->finish($queueItem);

        // Act
        $this->queue->fail($queueItem, 'Test failure description');

        $this->fail('Queue item status transition from "completed" to "failed" should not be allowed.');
    }

    /**
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\AbortTaskExecutionException
     */
    public function testItShouldBeForbiddenToTransitionFromCompletedToCompletedStatus()
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Illegal queue item state transition from "completed" to "completed"');

        // Arrange
        $queueItem = $this->queue->enqueue(
            'testQueue',
            new FooTask()
        );
        $this->queue->start($queueItem);
        $this->queue->finish($queueItem);

        // Act
        $this->queue->finish($queueItem);

        $this->fail('Queue item status transition from "completed" to "completed" should not be allowed.');
    }

    /**
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\AbortTaskExecutionException
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     */
    public function testItShouldBeForbiddenToTransitionFromFailedToAbortedStatus()
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Illegal queue item state transition from "failed" to "aborted"');

        $queueItem = $this->queue->enqueue('testQueue', new FooTask());

        $this->queue->start($queueItem);
        for ($i = 0; $i <= QueueService::MAX_RETRIES; $i++) {
            $this->queue->fail($queueItem, 'Test failure description');
            if ($i < QueueService::MAX_RETRIES) {
                $this->queue->start($queueItem);
            }
        }
        $this->queue->abort($queueItem, '');

        $this->fail('Queue item status transition from "Failed" to "Aborted" should not be allowed.');
    }

    public function testWhenStoringQueueItemFailsEnqueueMethodMustFail()
    {
        $this->expectException(QueueStorageUnavailableException::class);
        $this->expectExceptionMessage('Unable to update the task. Queue storage failed to save item.');

        // Arrange
        $this->queueStorage->disabled = true;

        // Act
        $this->queue->enqueue(
            'testQueue',
            new FooTask()
        );

        $this->fail(
            'Enqueue queue item must fail with QueueStorageUnavailableException when queue storage save fails.'
        );
    }

    public function testReportAliveParentReportAlive()
    {
        // Arrange
        $item = $this->queue->enqueue('test', new FooOrchestrator());
        $this->queue->start($item);
        /** @var FooOrchestrator $orchestrator */
        $orchestrator = $item->getTask();
        $this->queue->batchStatusUpdate([$orchestrator->taskList[0]->getExecutionId()], QueueItem::IN_PROGRESS);
        $subjob = $this->queue->find($orchestrator->taskList[0]->getExecutionId());

        // Act
        $subjob->getTask()->reportAlive();

        // Assert
        $saved = $this->queue->find($item->getId());
        self::assertNotNull($saved->getLastUpdateTimestamp());
    }

    public function testReportProgressParentReportedProgress()
    {
        // Arrange
        $item = $this->queue->enqueue('test', new FooOrchestrator());
        $this->queue->start($item);
        /** @var FooOrchestrator $orchestrator */
        $orchestrator = $item->getTask();
        $this->queue->batchStatusUpdate([$orchestrator->taskList[0]->getExecutionId()], QueueItem::IN_PROGRESS);
        $subjob = $this->queue->find($orchestrator->taskList[0]->getExecutionId());

        // Act
        $subjob->getTask()->reportProgress(100);

        // Assert
        $saved = $this->queue->find($item->getId());
        self::assertEquals(33.33, $saved->getProgressFormatted());
    }

    /**
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\AbortTaskExecutionException
     */
    public function testWhenStoringQueueItemFailsStartMethodMustFail()
    {
        $this->expectException(QueueStorageUnavailableException::class);
        $this->expectExceptionMessage('Unable to update the task. Queue storage failed to save item.');

        // Arrange
        $queueItem = $this->queue->enqueue(
            'testQueue',
            new FooTask()
        );
        $this->queueStorage->disabled = true;

        // Act
        $this->queue->start($queueItem);

        $this->fail(
            'Starting queue item must fail with QueueStorageUnavailableException when queue storage save fails.'
        );
    }

    /**
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\AbortTaskExecutionException
     */
    public function testWhenStoringQueueItemFailsFailMethodMustFail()
    {
        $this->expectException(QueueStorageUnavailableException::class);
        $this->expectExceptionMessage('Unable to update the task. Queue storage failed to save item.');

        // Arrange
        $queueItem = $this->queue->enqueue(
            'testQueue',
            new FooTask()
        );
        $this->queue->start($queueItem);
        $this->queueStorage->disabled = true;

        // Act
        $this->queue->fail($queueItem, 'Test failure description.');

        $this->fail(
            'Failing queue item must fail with QueueStorageUnavailableException when queue storage save fails.'
        );
    }

    /**
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\AbortTaskExecutionException
     */
    public function testWhenStoringQueueItemProgressFailsProgressMethodMustFail()
    {
        $this->expectException(QueueStorageUnavailableException::class);
        $this->expectExceptionMessage('Unable to update the task. Queue storage failed to save item.');

        // Arrange
        $queueItem = $this->queue->enqueue(
            'testQueue',
            new FooTask()
        );
        $this->queue->start($queueItem);
        $this->queueStorage->disabled = true;

        // Act
        $this->queue->updateProgress($queueItem, 2095);

        $this->fail(
            'Queue item progress update must fail with QueueStorageUnavailableException when queue storage save fails.'
        );
    }

    /**
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\AbortTaskExecutionException
     */
    public function testWhenStoringQueueItemAliveFailsAliveMethodMustFail()
    {
        $this->expectException(QueueStorageUnavailableException::class);
        $this->expectExceptionMessage('Unable to update the task. Queue storage failed to save item.');

        // Arrange
        $queueItem = $this->queue->enqueue('testQueue', new FooTask());
        $this->queue->start($queueItem);
        $this->queueStorage->disabled = true;

        // Act
        $this->queue->keepAlive($queueItem);

        $this->fail(
            'Queue item keep task alive signal must fail with QueueStorageUnavailableException when queue storage save fails.'
        );
    }

    /**
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\AbortTaskExecutionException
     */
    public function testWhenStoringQueueItemFailsFinishMethodMustFail()
    {
        $this->expectException(QueueStorageUnavailableException::class);
        $this->expectExceptionMessage('Unable to update the task. Queue storage failed to save item.');

        // Arrange
        $queueItem = $this->queue->enqueue(
            'testQueue',
            new FooTask()
        );
        $this->queue->start($queueItem);
        $this->queueStorage->disabled = true;

        // Act
        $this->queue->finish($queueItem);

        $this->fail(
            'Finishing queue item must fail with QueueStorageUnavailableException when queue storage save fails.'
        );
    }

    public function testCreate()
    {
        // Arrange
        $task = new FooTask();

        // Act
        $item = $this->queue->create("Test", $task);

        // Assert
        self::assertNotNull($item);
    }

    public function testCreateItemSaved()
    {
        // Arrange
        $task = new FooTask();

        // Act
        $item = $this->queue->create("Test", $task);

        // Assert
        $saved = $this->queue->find($item->getId());
        self::assertNotNull($saved);
    }

    public function testCreateTaskSet()
    {
        // Arrange
        $task = new FooTask();

        // Act
        $item = $this->queue->create("Test", $task);

        // Assert
        $saved = $this->queue->find($item->getId());
        self::assertNotNull($saved->getTask());
        self::assertEquals($item->getTask(), $saved->getTask());
    }

    public function testCreateQueueNameSet()
    {
        // Arrange
        $task = new FooTask();

        // Act
        $item = $this->queue->create("Test", $task);

        // Assert
        $saved = $this->queue->find($item->getId());
        self::assertEquals('Test', $saved->getQueueName());
    }

    public function testCreateContextSet()
    {
        // Arrange
        $task = new FooTask();

        // Act
        $item = $this->queue->create("Test", $task, 'TestCtx', Priority::HIGH);

        // Assert
        $saved = $this->queue->find($item->getId());
        self::assertEquals('TestCtx', $saved->getContext());
    }

    public function testCreatePrioritySet()
    {
        // Arrange
        $task = new FooTask();

        // Act
        $item = $this->queue->create("Test", $task, '', Priority::HIGH);

        // Assert
        $saved = $this->queue->find($item->getId());
        self::assertEquals(Priority::HIGH, $saved->getPriority());
    }

    public function testCreateParentSet()
    {
        // Arrange
        $task = new FooTask();

        // Act
        $item = $this->queue->create("Test", $task, '', Priority::HIGH, 1023);

        // Assert
        $saved = $this->queue->find($item->getId());
        self::assertEquals(1023, $saved->getParentId());
    }

    public function testBatchStatusUpdate()
    {
        // Arrange
        $item1 = $this->queue->create('test', new FooTask());
        $item2 = $this->queue->create('test', new FooTask());

        // Act
        $this->queue->batchStatusUpdate([$item1->getId(), $item2->getId()], QueueItem::QUEUED);

        // Assert
        $saved1 = $this->queue->find($item1->getId());
        $saved2 = $this->queue->find($item2->getId());

        self::assertEquals(QueueItem::QUEUED, $saved1->getStatus());
        self::assertEquals(QueueItem::QUEUED, $saved2->getStatus());
    }

    public function testBatchStatusUpdateProperSelection()
    {
        // Arrange
        $item1 = $this->queue->create('test', new FooTask());
        $item2 = $this->queue->create('test', new FooTask());
        $item3 = $this->queue->create('test', new FooTask());

        // Act
        $this->queue->batchStatusUpdate([$item3->getId(), $item2->getId()], QueueItem::QUEUED);

        // Assert
        $saved1 = $this->queue->find($item1->getId());

        self::assertEquals(QueueItem::CREATED, $saved1->getStatus());
    }

    public function testFailParentFailed()
    {
        // Arrange
        $item1 = $this->queue->create('test', new FooTask());
        $item2 = $this->queue->create('test', new FooTask(), '', 100, $item1->getId());
        $this->queue->batchStatusUpdate([$item1->getId(), $item2->getId()], QueueItem::IN_PROGRESS);
        $item2->setStatus(QueueItem::IN_PROGRESS);
        $item2->setRetries(TestQueueService::MAX_RETRIES);

        // Act
        $this->queue->fail($item2, 'Test reason.');

        // Assert
        $saved = $this->queue->find($item1->getId());
        self::assertEquals(QueueItem::FAILED, $saved->getStatus());
    }

    public function testFailGrandParentFailed()
    {
        // Arrange
        $item1 = $this->queue->create('test', new FooTask());
        $item2 = $this->queue->create('test', new FooTask(), '', 100, $item1->getId());
        $item3 = $this->queue->create('test', new FooTask(), '', 100, $item2->getId());
        $this->queue->batchStatusUpdate([$item1->getId(), $item2->getId(), $item3->getId()], QueueItem::IN_PROGRESS);
        $item3->setStatus(QueueItem::IN_PROGRESS);
        $item3->setRetries(TestQueueService::MAX_RETRIES);

        // Act
        $this->queue->fail($item3, 'Test reason.');

        // Assert
        $saved = $this->queue->find($item1->getId());
        self::assertEquals(QueueItem::FAILED, $saved->getStatus());
    }

    public function testAbortParentAborted()
    {
        // Arrange
        $item1 = $this->queue->create('test', new FooTask());
        $item2 = $this->queue->create('test', new FooTask(), '', 100, $item1->getId());

        // Act
        $this->queue->abort($item2, 'SubJob aborted');

        // Assert
        $saved = $this->queue->find($item1->getId());
        self::assertEquals(QueueItem::ABORTED, $saved->getStatus());
    }

    /**
     * @param \SeQura\Core\Infrastructure\TaskExecution\QueueItem $queueItem
     *
     * @throws \SeQura\Core\Infrastructure\ORM\Exceptions\EntityClassException
     * @throws \SeQura\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException
     */
    private function assertQueueItemIsSaved(QueueItem $queueItem)
    {
        $filter = new QueryFilter();
        $filter->where('id', '=', $queueItem->getId());
        /** @var QueueItem $storageItem */
        $storageItem = $this->queueStorage->selectOne($filter);

        $this->assertEquals(
            array(
                'id' => $queueItem->getId(),
                'status' => $queueItem->getStatus(),
                'type' => $queueItem->getTaskType(),
                'queueName' => $queueItem->getQueueName(),
                'context' => $queueItem->getContext(),
                'lastExecutionProgress' => $queueItem->getLastExecutionProgressBasePoints(),
                'progress' => $queueItem->getProgressBasePoints(),
                'retries' => $queueItem->getRetries(),
                'failureDescription' => $queueItem->getFailureDescription(),
                'createTimestamp' => $queueItem->getCreateTimestamp(),
                'queueTimestamp' => $queueItem->getQueueTimestamp(),
                'lastUpdateTimestamp' => $queueItem->getLastUpdateTimestamp(),
                'startTimestamp' => $queueItem->getStartTimestamp(),
                'finishTimestamp' => $queueItem->getFinishTimestamp(),
                'failTimestamp' => $queueItem->getFinishTimestamp(),
                'earliestStartTimestamp' => $queueItem->getEarliestStartTimestamp(),
            ),
            array(
                'id' => $storageItem->getId(),
                'status' => $storageItem->getStatus(),
                'type' => $storageItem->getTaskType(),
                'queueName' => $storageItem->getQueueName(),
                'context' => $storageItem->getContext(),
                'lastExecutionProgress' => $storageItem->getLastExecutionProgressBasePoints(),
                'progress' => $storageItem->getProgressBasePoints(),
                'retries' => $storageItem->getRetries(),
                'failureDescription' => $storageItem->getFailureDescription(),
                'createTimestamp' => $storageItem->getCreateTimestamp(),
                'queueTimestamp' => $storageItem->getQueueTimestamp(),
                'lastUpdateTimestamp' => $storageItem->getLastUpdateTimestamp(),
                'startTimestamp' => $storageItem->getStartTimestamp(),
                'finishTimestamp' => $storageItem->getFinishTimestamp(),
                'failTimestamp' => $storageItem->getFinishTimestamp(),
                'earliestStartTimestamp' => $storageItem->getEarliestStartTimestamp(),
            ),
            'Queue item storage data does not match queue item'
        );
    }

    /**
     * @param string $queueName
     * @param \SeQura\Core\Infrastructure\TaskExecution\Task $task
     *
     * @return \SeQura\Core\Infrastructure\TaskExecution\QueueItem
     *
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\AbortTaskExecutionException
     */
    private function generateRunningQueueItem($queueName, Task $task)
    {
        $queueItem = $this->queue->enqueue($queueName, $task);
        $this->queue->start($queueItem);

        return $queueItem;
    }

    /**
     * Checks whether queue item is in array.
     *
     * @param \SeQura\Core\Infrastructure\TaskExecution\QueueItem $needle
     * @param array $haystack
     *
     * @return bool
     */
    private function inArrayQueueItem(QueueItem $needle, array $haystack)
    {
        /** @var QueueItem $queueItem */
        foreach ($haystack as $queueItem) {
            if ($queueItem->getId() === $needle->getId()) {
                return true;
            }
        }

        return false;
    }
}
