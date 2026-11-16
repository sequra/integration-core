<?php

namespace SeQura\Core\Tests\BusinessLogic\TaskExecution\Standalone;

use DateTime;
use SeQura\Core\BusinessLogic\Domain\TaskExecution\Models\TaskExecutionConfig;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\TaskExecution\Standalone\TaskAdapter;
use SeQura\Core\BusinessLogic\TaskExecution\Standalone\TaskRunnerExecutor;
use SeQura\Core\Infrastructure\Serializer\Concrete\JsonSerializer;
use SeQura\Core\Infrastructure\Serializer\Concrete\NativeSerializer;
use SeQura\Core\Infrastructure\Serializer\Serializer;
use SeQura\Core\Infrastructure\ORM\RepositoryRegistry;
use SeQura\Core\Infrastructure\ServiceRegister;
use SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException;
use SeQura\Core\Infrastructure\TaskExecution\Interfaces\Priority;
use SeQura\Core\Infrastructure\TaskExecution\QueueItem;
use SeQura\Core\Infrastructure\TaskExecution\QueueService;
use SeQura\Core\Infrastructure\Utility\TimeProvider;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\TaskExecution\FixedTaskMetadataProvider;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\TaskExecution\SampleBusinessTask;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\Utility\TestTimeProvider;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

class TaskRunnerExecutorTest extends BaseTestCase
{
    /**
     * @var QueueService
     */
    private $queue;

    protected function setUp(): void
    {
        parent::setUp();
        SampleBusinessTask::$observedStoreIds = [];
        $this->queue = ServiceRegister::getService(QueueService::class);
    }

    public function testQueuesTheJobWhereItsMetadataSays(): void
    {
        $item = $this->enqueueWith(new TaskExecutionConfig('queue-a', 50, 'store-7'));

        self::assertSame(QueueItem::QUEUED, $item->getStatus());
        self::assertSame('queue-a', $item->getQueueName());
        self::assertSame('store-7', $item->getContext());
        self::assertSame(Priority::NORMAL, $item->getPriority());
        self::assertSame('SampleBusinessTask', $item->getTaskType());
        self::assertNull($item->getEarliestStartTimestamp());
    }

    /**
     * @dataProvider priorities
     */
    public function testMapsThePriorityOntoTheRunnerPriorities(int $priority, int $expected): void
    {
        $item = $this->enqueueWith(new TaskExecutionConfig('queue-a', $priority, 'store-7'));

        self::assertSame($expected, $item->getPriority());
    }

    /**
     * @return array<string, int[]>
     */
    public function priorities(): array
    {
        return [
            '0' => [0, Priority::LOW],
            '33' => [33, Priority::LOW],
            '34' => [34, Priority::NORMAL],
            '66' => [66, Priority::NORMAL],
            '67' => [67, Priority::HIGH],
            '100' => [100, Priority::HIGH],
        ];
    }

    public function testQueuedJobIsFoundByItsOwnType(): void
    {
        $item = $this->enqueueWith(new TaskExecutionConfig('queue-a', 50, 'store-7'));

        $found = $this->queue->findLatestByType('SampleBusinessTask', 'store-7');

        self::assertNotNull($found);
        self::assertSame($item->getId(), $found->getId());
    }

    public function testDelayedJobGetsItsEarliestStartTimestamp(): void
    {
        $now = new DateTime('2030-01-01 10:00:00');
        TestTimeProvider::getInstance()->setCurrentLocalTime($now);
        $executor = $this->executor(new TaskExecutionConfig('queue-a', 50, 'store-7'));

        $executor->scheduleDelayed(new SampleBusinessTask(), 600);

        $items = RepositoryRegistry::getQueueItemRepository()->select();
        self::assertCount(1, $items);
        self::assertSame($now->getTimestamp() + 600, $items[0]->getEarliestStartTimestamp());
        self::assertSame('queue-a', $items[0]->getQueueName());
        self::assertSame('store-7', $items[0]->getContext());
        self::assertSame(Priority::NORMAL, $items[0]->getPriority());
    }

    public function testQueuedTaskRunsInTheStoreItWasScheduledFor(): void
    {
        $item = $this->enqueueWith(new TaskExecutionConfig('queue-a', 50, 'store-7'));

        StoreContext::doWithStore('store-1', static function () use ($item) {
            $item->getTask()->execute();
        });

        self::assertSame(['store-7'], SampleBusinessTask::$observedStoreIds);
    }

    /**
     * @dataProvider serializers
     */
    public function testStoredEntryThatIsNotABusinessJobFailsWhenReadBack(string $serializerClass): void
    {
        TestServiceRegister::registerService(Serializer::CLASS_NAME, static function () use ($serializerClass) {
            return new $serializerClass();
        });
        $item = new QueueItem();
        $item->setSerializedTask(Serializer::serialize(new TaskAdapter(new SampleBusinessTask(), 'store-7')));
        $item->setSerializedTask(
            str_replace(
                [str_replace('\\', '\\\\', SampleBusinessTask::class), SampleBusinessTask::class],
                [\stdClass::class, \stdClass::class],
                $item->getSerializedTask()
            )
        );

        $this->expectException(QueueItemDeserializationException::class);

        $item->getTask();
    }

    /**
     * @return array<string, string[]>
     */
    public function serializers(): array
    {
        return ['json' => [JsonSerializer::class], 'native' => [NativeSerializer::class]];
    }

    private function enqueueWith(TaskExecutionConfig $config): QueueItem
    {
        $this->executor($config)->enqueue(new SampleBusinessTask());
        $items = RepositoryRegistry::getQueueItemRepository()->select();
        self::assertCount(1, $items);

        return $items[0];
    }

    private function executor(TaskExecutionConfig $config): TaskRunnerExecutor
    {
        return new TaskRunnerExecutor($this->queue, new FixedTaskMetadataProvider($config));
    }
}
