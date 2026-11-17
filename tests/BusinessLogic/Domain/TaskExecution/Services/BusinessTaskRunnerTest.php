<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\TaskExecution\Services;

use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\TaskExecution\Exceptions\InvalidTaskPayloadException;
use SeQura\Core\BusinessLogic\Domain\TaskExecution\Models\TaskPayload;
use SeQura\Core\BusinessLogic\Domain\TaskExecution\Services\BusinessTaskRunner;
use SeQura\Core\BusinessLogic\TransactionLog\Services\TransactionLogRecorder;
use SeQura\Core\BusinessLogic\Domain\Translations\Model\BaseTranslatableException;
use SeQura\Core\Infrastructure\Logger\Interfaces\ShopLoggerAdapter;
use SeQura\Core\Infrastructure\Logger\Logger;
use RuntimeException;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\TaskExecution\ScriptedBusinessTask;
use SeQura\Core\Tests\BusinessLogic\Domain\TaskExecution\Runner\RunnerTask;
use SeQura\Core\Tests\BusinessLogic\Domain\TaskExecution\Runner\UnbuildableTask;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\Logger\TestShopLogger;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;
use SeQura\Core\Tests\BusinessLogic\TransactionLog\Mocks\TransactionLogFixtures;

/**
 * @package SeQura\Core\Tests\BusinessLogic\Domain\TaskExecution\Services
 */
class BusinessTaskRunnerTest extends BaseTestCase
{
    use TransactionLogFixtures;

    /**
     * @var BusinessTaskRunner
     */
    private $runner;

    protected function setUp(): void
    {
        parent::setUp();

        RunnerTask::reset();
        UnbuildableTask::$executions = 0;
        $this->runner = new BusinessTaskRunner(TestServiceRegister::getService(TransactionLogRecorder::class));
    }

    public function testRunsTheRebuiltJobOnce(): void
    {
        $this->runner->run($this->payload(RunnerTask::MODE_PLAIN, 'store-7'));

        self::assertSame(1, RunnerTask::$executions);
        self::assertSame('ORD-1', RunnerTask::$seenOrderRef);
    }

    public function testRunsInsideTheStoreItWasScheduledForAndRestoresTheCurrentOne(): void
    {
        StoreContext::doWithStore('store-1', function (): void {
            $this->runner->run($this->payload(RunnerTask::MODE_PLAIN, 'store-7'));

            self::assertSame('store-1', StoreContext::getInstance()->getStoreId());
        });

        self::assertSame('store-7', RunnerTask::$seenStore);
    }

    public function testRebuildsTheJobInsideTheStoreItWasScheduledFor(): void
    {
        StoreContext::doWithStore('store-1', function (): void {
            $this->runner->run($this->payload(RunnerTask::MODE_PLAIN, 'store-7'));
        });

        self::assertSame('store-7', RunnerTask::$storeWhenRebuilt);
    }

    public function testRunsInsideTheEmptyStore(): void
    {
        StoreContext::doWithStore('store-1', function (): void {
            $this->runner->run($this->payload(RunnerTask::MODE_PLAIN, ''));
        });

        self::assertSame('', RunnerTask::$seenStore);
    }

    public function testCompletesWhenTheJobReturnsNull(): void
    {
        $this->runner->run($this->payload(RunnerTask::MODE_NULL, 'store-7'));

        self::assertSame(1, RunnerTask::$executions);
    }

    public function testRunsAJobThatYieldsSignalsToTheEnd(): void
    {
        $this->runner->run($this->payload(RunnerTask::MODE_SIGNALS, 'store-7'));

        self::assertSame(['after-progress', 'after-keep-alive', 'end'], RunnerTask::$trace);
    }

    public function testLogsOnlyTheStatusMessagesToTheDebugLog(): void
    {
        $logger = new TestShopLogger();
        TestServiceRegister::registerService(ShopLoggerAdapter::CLASS_NAME, static function () use ($logger) {
            return $logger;
        });

        $this->runner->run($this->payload(RunnerTask::MODE_SIGNALS, 'store-7'));

        self::assertCount(1, $logger->loggedMessages);
        self::assertSame(Logger::DEBUG, $logger->loggedMessages[0]->getLogLevel());
        self::assertStringContainsString('syncing', $logger->loggedMessages[0]->getMessage());
        self::assertStringContainsString('RunnerTask', $logger->loggedMessages[0]->getMessage());
    }

    public function testLetsTheJobExceptionReachTheCallerAndRestoresTheStore(): void
    {
        StoreContext::doWithStore('store-1', function (): void {
            try {
                $this->runner->run($this->payload(RunnerTask::MODE_THROW, 'store-7'));
                self::fail('The job exception was expected.');
            } catch (\RuntimeException $e) {
                self::assertSame(RunnerTask::$thrown, $e);
            }

            self::assertSame('store-1', StoreContext::getInstance()->getStoreId());
        });
    }

    public function testKeepsTheStoreWhileTheGeneratorIsIterated(): void
    {
        StoreContext::doWithStore('store-1', function (): void {
            $this->runner->run($this->payload(RunnerTask::MODE_YIELD_THEN_READ_STORE, 'store-7'));
        });

        self::assertSame('store-7', RunnerTask::$seenStore);
    }

    public function testLetsALateGeneratorExceptionReachTheCallerAndRestoresTheStore(): void
    {
        StoreContext::doWithStore('store-1', function (): void {
            try {
                $this->runner->run($this->payload(RunnerTask::MODE_YIELD_THEN_THROW, 'store-7'));
                self::fail('The job exception was expected.');
            } catch (\RuntimeException $e) {
                self::assertSame(RunnerTask::$thrown, $e);
            }

            self::assertSame('store-1', StoreContext::getInstance()->getStoreId());
        });
    }

    /**
     * @dataProvider invalidPayloads
     *
     * @param array<string, mixed> $changes
     * @param string[] $removed
     */
    public function testRejectsAnInvalidPayloadWithoutRunningAnything(array $changes, array $removed): void
    {
        $data = array_merge($this->payload(RunnerTask::MODE_PLAIN, 'store-7'), $changes);
        foreach ($removed as $key) {
            unset($data[$key]);
        }

        try {
            $this->runner->run($data);
            self::fail('InvalidTaskPayloadException was expected.');
        } catch (InvalidTaskPayloadException $e) {
            self::assertInstanceOf(BaseTranslatableException::class, $e);
            self::assertSame(
                'general.errors.taskExecution.invalidTaskPayload',
                $e->getTranslatableLabel()->getCode()
            );
        }

        self::assertSame(0, RunnerTask::$executions);
    }

    /**
     * @return array<string, array{0: array<string, mixed>, 1: string[]}>
     */
    public function invalidPayloads(): array
    {
        return [
            'not a business job' => [['task_class' => \stdClass::class], []],
            'task_data not an array' => [['task_data' => 'ORD-1'], []],
            'context not a string' => [['context' => 7], []],
            'context missing' => [[], ['context']],
        ];
    }

    public function testRejectsAJobThatCannotBeRebuiltFromItsData(): void
    {
        try {
            $this->runner->run(['task_class' => UnbuildableTask::class, 'task_data' => [], 'context' => '']);
            self::fail('InvalidTaskPayloadException was expected.');
        } catch (InvalidTaskPayloadException $e) {
            self::assertSame(UnbuildableTask::$error, $e->getPrevious());
        }

        self::assertSame(0, UnbuildableTask::$executions);
    }

    public function testFailingJobLeavesAFailedTransactionLogRowAndRethrows(): void
    {
        $error = new RuntimeException('boom');
        ScriptedBusinessTask::$script = static function () use ($error) {
            throw $error;
        };

        try {
            $this->runner->run($this->scriptedPayload());
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
        ScriptedBusinessTask::$script = function () use (&$statuses) {
            yield null;
            $statuses = array_map(static function ($row) {
                return $row->getQueueStatus();
            }, $this->transactionLogRows('store-7'));
        };

        $this->runner->run($this->scriptedPayload());

        self::assertSame(['in_progress'], $statuses);
        self::assertSame([], $this->transactionLogRows('store-7'));
    }

    /**
     * @return array<string, mixed>
     */
    private function scriptedPayload(): array
    {
        return json_decode(json_encode(TaskPayload::fromTask(new ScriptedBusinessTask(), 'store-7')->toArray()), true);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(string $mode, string $context): array
    {
        return json_decode(json_encode(TaskPayload::fromTask(new RunnerTask($mode), $context)->toArray()), true);
    }
}
