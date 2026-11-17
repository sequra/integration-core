<?php

namespace SeQura\Core\Tests\BusinessLogic\TaskExecution\Standalone;

use InvalidArgumentException;
use RuntimeException;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\TaskExecution\Standalone\TaskAdapter;
use SeQura\Core\Infrastructure\Logger\Interfaces\ShopLoggerAdapter;
use SeQura\Core\Infrastructure\Logger\Logger;
use SeQura\Core\Infrastructure\Serializer\Concrete\JsonSerializer;
use SeQura\Core\Infrastructure\Serializer\Concrete\NativeSerializer;
use SeQura\Core\Infrastructure\Serializer\Serializer;
use SeQura\Core\Infrastructure\TaskExecution\TaskEvents\AliveAnnouncedTaskEvent;
use SeQura\Core\Infrastructure\TaskExecution\TaskEvents\TaskProgressEvent;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\TaskExecution\SampleBusinessTask;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\TaskExecution\ScriptedBusinessTask;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\Logger\TestShopLogger;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;
use SeQura\Core\Tests\BusinessLogic\TransactionLog\Mocks\TransactionLogFixtures;

class TaskAdapterTest extends BaseTestCase
{
    use TransactionLogFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        ScriptedBusinessTask::$script = null;
        SampleBusinessTask::$observedStoreIds = [];
    }

    public function testYieldedNumbersAreReportedAsProgress(): void
    {
        $basePoints = [];
        $adapter = $this->scripted(static function () {
            yield 40;
        });
        $adapter->when(TaskProgressEvent::class, static function (TaskProgressEvent $event) use (&$basePoints) {
            $basePoints[] = $event->getProgressBasePoints();
        });

        $adapter->execute();

        self::assertSame([4000], $basePoints);
    }

    public function testYieldedNullKeepsTheJobAlive(): void
    {
        $alive = 0;
        $adapter = $this->scripted(static function () {
            yield null;
        });
        $adapter->when(AliveAnnouncedTaskEvent::class, static function () use (&$alive) {
            $alive++;
        });

        $adapter->execute();

        self::assertSame(1, $alive);
    }

    public function testYieldedStringKeepsTheJobAliveAndIsLogged(): void
    {
        $logger = $this->registerLogger();
        $alive = 0;
        $adapter = $this->scripted(static function () {
            yield 'syncing';
        });
        $adapter->when(AliveAnnouncedTaskEvent::class, static function () use (&$alive) {
            $alive++;
        });

        $adapter->execute();

        self::assertSame(1, $alive);
        self::assertCount(1, $logger->loggedMessages);
        self::assertSame(Logger::DEBUG, $logger->loggedMessages[0]->getLogLevel());
        self::assertStringContainsString('syncing', $logger->loggedMessages[0]->getMessage());
        self::assertStringContainsString('ScriptedBusinessTask', $logger->loggedMessages[0]->getMessage());
    }

    public function testJobWithoutGeneratorJustRuns(): void
    {
        $calls = 0;
        $progress = 0;
        $adapter = $this->scripted(static function () use (&$calls) {
            $calls++;

            return null;
        });
        $adapter->when(TaskProgressEvent::class, static function () use (&$progress) {
            $progress++;
        });

        $adapter->execute();

        self::assertSame(1, $calls);
        self::assertSame(0, $progress);
    }

    public function testRunsInsideTheStoreItWasScheduledFor(): void
    {
        $adapter = new TaskAdapter(new SampleBusinessTask(), 'store-7');

        StoreContext::doWithStore('store-1', static function () use ($adapter) {
            $adapter->execute();
        });

        self::assertSame(['store-7'], SampleBusinessTask::$observedStoreIds);
        self::assertSame('', StoreContext::getInstance()->getStoreId());
    }

    public function testGeneratorBodyRunsInsideTheJobStore(): void
    {
        $observed = [];
        $adapter = $this->scripted(static function () use (&$observed) {
            yield null;
            $observed[] = StoreContext::getInstance()->getStoreId();
        }, 'store-7');

        StoreContext::doWithStore('store-1', static function () use ($adapter) {
            $adapter->execute();
        });

        self::assertSame(['store-7'], $observed);
    }

    public function testSignalsAreHandledAsTheyAreYielded(): void
    {
        $steps = [];
        $adapter = $this->scripted(static function () use (&$steps) {
            yield 10;
            $steps[] = 'after-first';
            yield 20;
            $steps[] = 'end';
        });
        $seen = [];
        $adapter->when(TaskProgressEvent::class, static function (TaskProgressEvent $event) use (&$seen, &$steps) {
            $seen[] = [$event->getProgressBasePoints(), $steps];
        });

        $adapter->execute();

        self::assertSame([[1000, []], [2000, ['after-first']]], $seen);
        self::assertSame(['after-first', 'end'], $steps);
    }

    public function testJobFailureReachesTheRunnerUnchanged(): void
    {
        $exception = new RuntimeException('boom');
        $adapter = $this->scripted(static function () use ($exception) {
            throw $exception;
        });

        try {
            $adapter->execute();
            self::fail('Expected exception.');
        } catch (RuntimeException $caught) {
            self::assertSame($exception, $caught);
        }
    }

    public function testStoredFormIsThePayloadShape(): void
    {
        self::assertSame(
            [
                'task_class' => SampleBusinessTask::class,
                'task_data' => ['orderRef' => 'ORD-1'],
                'context' => 'store-7',
            ],
            (new TaskAdapter(new SampleBusinessTask(), 'store-7'))->toArray()
        );
    }

    /**
     * @dataProvider serializers
     */
    public function testSurvivesASerializationRoundTrip(string $serializerClass): void
    {
        TestServiceRegister::registerService(Serializer::CLASS_NAME, static function () use ($serializerClass) {
            return new $serializerClass();
        });
        $adapter = new TaskAdapter(new SampleBusinessTask(), 'store-7');

        $restored = Serializer::unserialize(Serializer::serialize($adapter));

        self::assertInstanceOf(TaskAdapter::class, $restored);
        self::assertSame($adapter->toArray(), $restored->toArray());
    }

    /**
     * @return array<string, string[]>
     */
    public function serializers(): array
    {
        return ['json' => [JsonSerializer::class], 'native' => [NativeSerializer::class]];
    }

    /**
     * @dataProvider invalidClasses
     */
    public function testRejectsEntriesThatAreNotBusinessJobs(string $taskClass): void
    {
        $this->expectException(InvalidArgumentException::class);

        TaskAdapter::fromArray(['task_class' => $taskClass, 'task_data' => [], 'context' => 'store-7']);
    }

    /**
     * @return array<string, string[]>
     */
    public function invalidClasses(): array
    {
        return [
            'unknown class' => ['SeQura\Core\Tests\DoesNotExist'],
            'not a business job' => [\stdClass::class],
        ];
    }

    /**
     * @dataProvider malformedPayloads
     *
     * @param array<string, mixed> $payload
     */
    public function testRejectsPayloadsWithWrongFieldTypes(array $payload): void
    {
        $this->expectException(InvalidArgumentException::class);

        TaskAdapter::fromArray(['task_class' => SampleBusinessTask::class] + $payload);
    }

    /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function malformedPayloads(): array
    {
        return [
            'scalar task data' => [['task_data' => 'oops', 'context' => 'store-7']],
            'non-string context' => [['task_data' => [], 'context' => 7]],
        ];
    }

    public function testTypeIsTheJobShortClassName(): void
    {
        self::assertSame('SampleBusinessTask', (new TaskAdapter(new SampleBusinessTask(), ''))->getType());
    }

    public function testFailingJobLeavesAFailedTransactionLogRowAndRethrows(): void
    {
        $error = new RuntimeException('boom');
        $adapter = $this->scripted(static function () use ($error) {
            throw $error;
        });

        try {
            $adapter->execute();
            self::fail('Expected exception');
        } catch (RuntimeException $e) {
            self::assertSame($error, $e);
        }

        $rows = $this->transactionLogRows('store-7');
        self::assertCount(1, $rows);
        self::assertSame('ScriptedBusinessTask', $rows[0]->getEventCode());
        self::assertSame('failed', $rows[0]->getQueueStatus());
        self::assertSame('boom', $rows[0]->getReason());
    }

    public function testGeneratorBodyRunsWhileTheTransactionLogRowIsInProgress(): void
    {
        $statuses = [];
        $adapter = $this->scripted(function () use (&$statuses) {
            yield null;
            $statuses = array_map(static function ($row) {
                return $row->getQueueStatus();
            }, $this->transactionLogRows('store-7'));
        });

        $adapter->execute();

        self::assertSame(['in_progress'], $statuses);
        self::assertSame([], $this->transactionLogRows('store-7'));
    }

    private function scripted(callable $script, string $context = 'store-7'): TaskAdapter
    {
        ScriptedBusinessTask::$script = $script;

        return new TaskAdapter(new ScriptedBusinessTask(), $context);
    }

    private function registerLogger(): TestShopLogger
    {
        $logger = new TestShopLogger();
        TestServiceRegister::registerService(ShopLoggerAdapter::CLASS_NAME, static function () use ($logger) {
            return $logger;
        });

        return $logger;
    }
}
