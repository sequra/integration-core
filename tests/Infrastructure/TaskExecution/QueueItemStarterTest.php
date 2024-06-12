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
use SeQura\Core\Infrastructure\TaskExecution\Events\QueueItemStateTransitionEventBus;
use SeQura\Core\Infrastructure\TaskExecution\Interfaces\TaskRunnerWakeup;
use SeQura\Core\Infrastructure\TaskExecution\QueueItem;
use SeQura\Core\Infrastructure\TaskExecution\QueueItemStarter;
use SeQura\Core\Infrastructure\TaskExecution\QueueService;
use SeQura\Core\Infrastructure\Utility\Events\EventBus;
use SeQura\Core\Infrastructure\Utility\TimeProvider;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\Logger\TestDefaultLogger;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\Logger\TestShopLogger;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\ORM\MemoryQueueItemRepository;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\ORM\MemoryRepository;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution\FooTask;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution\TestQueueService;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution\TestTaskRunnerWakeupService;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TestConfigurationManager;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TestHttpClient;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TestShopConfiguration;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\Utility\TestTimeProvider;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;
use PHPUnit\Framework\TestCase;

/**
 * Class QueueItemStarterTest
 *
 * @package SeQura\Core\Tests\Infrastructure\TaskExecution
 */
class QueueItemStarterTest extends TestCase
{
    /** @var \SeQura\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution\TestQueueService */
    public $queue;
    /** @var MemoryQueueItemRepository */
    public $queueStorage;
    /** @var TestTimeProvider */
    public $timeProvider;
    /** @var \SeQura\Core\Tests\Infrastructure\Common\TestComponents\Logger\TestShopLogger */
    public $logger;
    /** @var Configuration */
    public $shopConfiguration;
    /** @var ConfigurationManager */
    public $configurationManager;

    /**
     * @throws \Exception
     */
    public function setUp(): void
    {
        RepositoryRegistry::registerRepository(QueueItem::CLASS_NAME, MemoryQueueItemRepository::getClassName());
        RepositoryRegistry::registerRepository(ConfigEntity::CLASS_NAME, MemoryRepository::getClassName());

        $timeProvider = new TestTimeProvider();
        $queue = new TestQueueService();
        $shopLogger = new TestShopLogger();
        $configurationManager = new TestConfigurationManager();
        $shopConfiguration = new TestShopConfiguration();
        $serializer = new NativeSerializer();

        new TestServiceRegister(
            array(
                ConfigurationManager::CLASS_NAME => function () use ($configurationManager) {
                    return $configurationManager;
                },
                TimeProvider::CLASS_NAME => function () use ($timeProvider) {
                    return $timeProvider;
                },
                TaskRunnerWakeup::CLASS_NAME => function () {
                    return new TestTaskRunnerWakeupService();
                },
                QueueService::CLASS_NAME => function () use ($queue) {
                    return $queue;
                },
                EventBus::CLASS_NAME => function () {
                    return EventBus::getInstance();
                },
                DefaultLoggerAdapter::CLASS_NAME => function () {
                    return new TestDefaultLogger();
                },
                ShopLoggerAdapter::CLASS_NAME => function () use ($shopLogger) {
                    return $shopLogger;
                },
                Configuration::CLASS_NAME => function () use ($shopConfiguration) {
                    return $shopConfiguration;
                },
                HttpClient::CLASS_NAME => function () {
                    return new TestHttpClient();
                },
                Serializer::CLASS_NAME => function () use ($serializer) {
                    return $serializer;
                },
                QueueItemStateTransitionEventBus::CLASS_NAME => function () {
                    return QueueItemStateTransitionEventBus::getInstance();
                },
            )
        );


        // Initialize logger component with new set of log adapters
        Logger::resetInstance();

        $shopConfiguration->setIntegrationName('Shop1');

        $this->queueStorage = RepositoryRegistry::getQueueItemRepository();
        $this->timeProvider = $timeProvider;
        $this->queue = $queue;
        $this->logger = $shopLogger;
        $this->shopConfiguration = $shopConfiguration;
        $this->configurationManager = $configurationManager;
    }

    /**
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     */
    public function testRunningItemStarter()
    {
        // Arrange
        $queueItem = $this->queue->enqueue(
            'test',
            new FooTask()
        );
        $itemStarter = new QueueItemStarter($queueItem->getId());

        // Act
        $itemStarter->run();

        // Assert
        $findCallHistory = $this->queue->getMethodCallHistory('find');
        $startCallHistory = $this->queue->getMethodCallHistory('start');
        self::assertCount(1, $findCallHistory);
        self::assertCount(1, $startCallHistory);
        self::assertEquals($queueItem->getId(), $findCallHistory[0]['id']);
        /** @var QueueItem $startedQueueItem */
        $startedQueueItem = $startCallHistory[0]['queueItem'];
        self::assertEquals($queueItem->getId(), $startedQueueItem->getId());
    }

    /**
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     */
    public function testItemStarterMustBeRunnableAfterDeserialization()
    {
        // Arrange
        $queueItem = $this->queue->enqueue(
            'test',
            new FooTask()
        );
        $itemStarter = new QueueItemStarter($queueItem->getId());
        /** @var QueueItemStarter $unserializedItemStarter */
        $unserializedItemStarter = Serializer::unserialize(Serializer::serialize($itemStarter));

        // Act
        $unserializedItemStarter->run();

        // Assert
        $findCallHistory = $this->queue->getMethodCallHistory('find');
        $startCallHistory = $this->queue->getMethodCallHistory('start');
        self::assertCount(1, $findCallHistory);
        self::assertCount(1, $startCallHistory);
        self::assertEquals($queueItem->getId(), $findCallHistory[0]['id']);
        /** @var QueueItem $startedQueueItem */
        $startedQueueItem = $startCallHistory[0]['queueItem'];
        self::assertEquals($queueItem->getId(), $startedQueueItem->getId());
    }

    /**
     * @throws \SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     */
    public function testItemsStarterMustSetTaskExecutionContextInConfiguration()
    {
        // Arrange
        $queueItem = $this->queue->enqueue('test', new FooTask(), 'test');
        $itemStarter = new QueueItemStarter($queueItem->getId());

        // Act
        $itemStarter->run();

        // Assert
        self::assertSame(
            'test',
            $this->configurationManager->getContext(),
            'Item starter must set task context before task execution.'
        );
    }
}
