<?php

namespace SeQura\Core\Tests\BusinessLogic\TransactionLog\Services;

use DateTime;
use LogicException;
use RuntimeException;
use SeQura\Core\BusinessLogic\AdminAPI\AdminAPI;
use SeQura\Core\BusinessLogic\DataAccess\TransactionLog\Entities\TransactionLog;
use SeQura\Core\BusinessLogic\Domain\Integration\TaskExecution\BusinessTaskInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\TaskExecution\TaskExecutorInterface;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\OrderNotFoundException;
use SeQura\Core\BusinessLogic\Domain\Order\Service\OrderService;
use SeQura\Core\BusinessLogic\TransactionLog\Models\TransactionData;
use SeQura\Core\BusinessLogic\TransactionLog\RepositoryContracts\TransactionLogRepositoryInterface;
use SeQura\Core\BusinessLogic\TransactionLog\Services\TransactionLogRecorder;
use SeQura\Core\BusinessLogic\TransactionLog\Services\TransactionLogService;
use SeQura\Core\BusinessLogic\TransactionLog\Tasks\TransactionalOrderUpdateTask;
use SeQura\Core\BusinessLogic\Webhook\Services\ShopOrderService;
use SeQura\Core\Infrastructure\Utility\TimeProvider;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\TaskExecution\SampleBusinessTask;
use SeQura\Core\Tests\BusinessLogic\TransactionLog\Mocks\MockOrderUpdateData;
use SeQura\Core\Tests\BusinessLogic\TransactionLog\Mocks\TransactionLogFixtures;
use SeQura\Core\Tests\BusinessLogic\WebhookAPI\MockComponents\MockShopOrderService;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\Utility\TestTimeProvider;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;
use Throwable;
use TypeError;

class TransactionLogRecorderTest extends BaseTestCase
{
    use TransactionLogFixtures;

    /**
     * @var TransactionLogRecorder
     */
    private $recorder;

    /**
     * @var TestTimeProvider
     */
    private $clock;

    /**
     * @var TaskExecutorInterface
     */
    private $executor;

    protected function setUp(): void
    {
        parent::setUp();

        TestServiceRegister::registerService(ShopOrderService::class, static function () {
            return new MockShopOrderService();
        });

        $this->clock = TestServiceRegister::getService(TimeProvider::class);
        $this->clock->setCurrentLocalTime(new DateTime());
        $this->executor = new class implements TaskExecutorInterface {
            /**
             * @var BusinessTaskInterface[]
             */
            public $enqueued = [];

            /**
             * @var array<int|null>
             */
            public $idsAtEnqueue = [];

            /**
             * @var Throwable|null
             */
            public $error;

            public function enqueue(BusinessTaskInterface $task): void
            {
                $this->enqueued[] = $task;
                $this->idsAtEnqueue[] = $task->getTransactionLogId();
                if ($this->error !== null) {
                    throw $this->error;
                }
            }

            public function scheduleDelayed(BusinessTaskInterface $task, int $delaySeconds): void
            {
            }
        };

        $this->recorder = new TransactionLogRecorder(
            TestServiceRegister::getService(TransactionLogService::class),
            $this->executor,
            $this->clock
        );
    }

    public function testScheduleStoresAQueuedRowBeforeEnqueueing(): void
    {
        $this->storeOrder('ref-1', 'store-1');
        $job = $this->orderJob('ref-1');

        StoreContext::doWithStore('store-1', function () use ($job) {
            $this->recorder->schedule($job);
        });

        $rows = $this->transactionLogRows('store-1');
        self::assertCount(1, $rows);
        self::assertSame('ref-1', $rows[0]->getMerchantReference());
        self::assertSame('shipped', $rows[0]->getEventCode());
        self::assertSame($job->getTransactionData()->getTimestamp(), $rows[0]->getTimestamp());
        self::assertSame(TransactionLog::QUEUED, $rows[0]->getQueueStatus());
        self::assertSame($rows[0]->getId(), $rows[0]->getExecutionId());
        self::assertCount(1, $this->executor->enqueued);
        self::assertSame([$rows[0]->getId()], $this->executor->idsAtEnqueue);
    }

    public function testScheduleRemovesTheRowWhenEnqueueingFails(): void
    {
        $this->storeOrder('ref-1', 'store-1');
        $this->executor->error = new RuntimeException('queue down');

        $this->expectSameException($this->executor->error, function () {
            StoreContext::doWithStore('store-1', function () {
                $this->recorder->schedule($this->orderJob('ref-1'));
            });
        });

        self::assertSame([], $this->transactionLogRows('store-1'));
    }

    public function testScheduleRefusesUnknownOrders(): void
    {
        try {
            StoreContext::doWithStore('store-1', function () {
                $this->recorder->schedule($this->orderJob('ref-9'));
            });
            self::fail('Expected exception');
        } catch (OrderNotFoundException $e) {
            self::assertSame([], $this->transactionLogRows('store-1'));
            self::assertSame([], $this->executor->enqueued);
        }
    }

    public function testOrderJobIsInProgressWhileRunningAndGoneAfterwards(): void
    {
        $job = $this->scheduledOrderJob();
        $ran = 0;
        $statuses = [];

        $this->around($job, 'store-1', function () use (&$ran, &$statuses) {
            $ran++;
            $statuses = array_map(static function (TransactionLog $log) {
                return $log->getQueueStatus();
            }, $this->transactionLogRows('store-1'));
        });

        self::assertSame(1, $ran);
        self::assertSame([TransactionLog::IN_PROGRESS], $statuses);
        self::assertSame([], $this->transactionLogRows('store-1'));
    }

    public function testFailedOrderJobKeepsItsRowAndRethrows(): void
    {
        $job = $this->scheduledOrderJob();
        $id = $job->getTransactionLogId();
        $error = new RuntimeException('boom');

        $this->expectSameException($error, function () use ($job, $error) {
            $this->around($job, 'store-1', static function () use ($error) {
                throw $error;
            });
        });

        $rows = $this->transactionLogRows('store-1');
        self::assertCount(1, $rows);
        self::assertSame($id, $rows[0]->getId());
        self::assertSame(TransactionLog::FAILED, $rows[0]->getQueueStatus());
        self::assertSame('boom', $rows[0]->getReason());
        self::assertSame('Order update action not supported on SeQura.', $rows[0]->getFailureDescription());
    }

    public function testRetriedOrderJobReusesItsRow(): void
    {
        $job = $this->scheduledOrderJob();
        $id = $job->getTransactionLogId();
        $error = new RuntimeException('boom');
        $this->expectSameException($error, function () use ($job, $error) {
            $this->around($job, 'store-1', static function () use ($error) {
                throw $error;
            });
        });
        $seen = [];

        $this->around($job, 'store-1', function () use (&$seen) {
            $seen = $this->transactionLogRows('store-1');
        });

        self::assertCount(1, $seen);
        self::assertSame($id, $seen[0]->getId());
        self::assertSame(TransactionLog::IN_PROGRESS, $seen[0]->getQueueStatus());
        self::assertSame([], $this->transactionLogRows('store-1'));
    }

    public function testOrderJobThatWasNeverScheduledGetsItsRowWhenItRuns(): void
    {
        $this->storeOrder('ref-1', 'store-1');
        $job = $this->orderJob('ref-1');
        self::assertNull($job->getTransactionLogId());
        $seen = [];

        $this->around($job, 'store-1', function () use (&$seen) {
            $seen = $this->transactionLogRows('store-1');
        });

        self::assertCount(1, $seen);
        self::assertSame('store-1', $seen[0]->getStoreId());
        self::assertSame('ref-1', $seen[0]->getMerchantReference());
        self::assertSame(TransactionLog::IN_PROGRESS, $seen[0]->getQueueStatus());
        self::assertSame($seen[0]->getId(), $job->getTransactionLogId());
    }

    public function testJobErrorSurvivesAFailureToRecordIt(): void
    {
        $failingService = new class (
            TestServiceRegister::getService(TransactionLogRepositoryInterface::class),
            TestServiceRegister::getService(OrderService::class),
            new MockShopOrderService()
        ) extends TransactionLogService {
            public function update(TransactionLog $transactionLog): void
            {
                if ($transactionLog->getQueueStatus() === TransactionLog::FAILED) {
                    throw new LogicException('log store down');
                }

                parent::update($transactionLog);
            }
        };
        $recorder = new TransactionLogRecorder(
            $failingService,
            $this->executor,
            TestServiceRegister::getService(TimeProvider::class)
        );
        $error = new RuntimeException('boom');

        $this->expectSameException($error, static function () use ($recorder, $error) {
            StoreContext::doWithStore('store-7', static function () use ($recorder, $error) {
                $recorder->around(new SampleBusinessTask(), static function () use ($error) {
                    throw $error;
                });
            });
        });
    }

    public function testJobWithoutOrderShowsItsNameWhileRunning(): void
    {
        $before = time();
        $seen = [];

        $this->around(new SampleBusinessTask(), 'store-7', function () use (&$seen) {
            $seen = $this->transactionLogRows('store-7');
        });

        self::assertCount(1, $seen);
        $row = $seen[0];
        self::assertSame('SampleBusinessTask', $row->getEventCode());
        self::assertSame('store-7', $row->getStoreId());
        self::assertSame(TransactionLog::IN_PROGRESS, $row->getQueueStatus());
        self::assertGreaterThanOrEqual($before, $row->getTimestamp());
        self::assertSame($row->getId(), $row->getExecutionId());
        self::assertFalse($row->isSuccessful());
        self::assertOrderFieldsEmpty($row);
        self::assertSame([], $this->transactionLogRows('store-7'));
    }

    public function testFailedJobWithoutOrderLeavesAFailedRow(): void
    {
        $error = new RuntimeException('boom');

        $this->expectSameException($error, function () use ($error) {
            $this->around(new SampleBusinessTask(), 'store-7', static function () use ($error) {
                throw $error;
            });
        });

        $rows = $this->transactionLogRows('store-7');
        self::assertCount(1, $rows);
        self::assertSame('SampleBusinessTask', $rows[0]->getEventCode());
        self::assertSame(TransactionLog::FAILED, $rows[0]->getQueueStatus());
        self::assertSame('boom', $rows[0]->getReason());
        self::assertFalse($rows[0]->isSuccessful());
        self::assertSame('', $rows[0]->getFailureDescription());
    }

    public function testSuccessClearsEarlierFailuresOfTheSameJobInTheSameStoreOnly(): void
    {
        $this->failedRow('SampleBusinessTask', 'store-7');
        $this->failedRow('SampleBusinessTask', 'store-9');
        $this->failedRow('OtherBusinessTask', 'store-7');

        $this->around(new SampleBusinessTask(), 'store-7', static function () {
        });

        self::assertSame(['OtherBusinessTask'], $this->eventCodes('store-7'));
        self::assertSame(['SampleBusinessTask'], $this->eventCodes('store-9'));
    }

    public function testThrowableThatIsNotAnExceptionIsRecordedAndRethrown(): void
    {
        $error = new TypeError('bad type');

        $this->expectSameException($error, function () use ($error) {
            $this->around(new SampleBusinessTask(), 'store-7', static function () use ($error) {
                throw $error;
            });
        });

        $rows = $this->transactionLogRows('store-7');
        self::assertCount(1, $rows);
        self::assertSame(TransactionLog::FAILED, $rows[0]->getQueueStatus());
    }

    public function testRowsOlderThanOneMonthArePurgedAsJobsRun(): void
    {
        $this->clock->setCurrentLocalTime(new DateTime('2025-06-15'));
        $this->rowAt('Old', (new DateTime('2025-05-14'))->getTimestamp());
        $this->rowAt('Recent', (new DateTime('2025-06-14'))->getTimestamp());

        $this->around(new SampleBusinessTask(), 'store-7', static function () {
        });

        self::assertSame(['Recent'], $this->eventCodes('store-7'));
    }

    public function testRowPastThePurgeCutoffIsBuiltAgain(): void
    {
        $this->storeOrder('ref-1', 'store-1');
        $data = new TransactionData('ref-1', 'shipped', (new DateTime('-40 days'))->getTimestamp(), 'r', true);
        $job = new TransactionalOrderUpdateTask(MockOrderUpdateData::getOrderUpdateData(), $data);
        StoreContext::doWithStore('store-1', function () use ($job) {
            $this->recorder->schedule($job);
        });
        $oldId = $job->getTransactionLogId();
        $seen = [];

        $this->around($job, 'store-1', function () use (&$seen) {
            $seen = $this->transactionLogRows('store-1');
        });

        self::assertCount(1, $seen);
        self::assertNotSame($oldId, $seen[0]->getId());
        self::assertSame($seen[0]->getId(), $job->getTransactionLogId());
    }

    public function testFailedJobIsExposedByTheAdminApi(): void
    {
        $error = new RuntimeException('boom');

        $this->expectSameException($error, function () use ($error) {
            $this->around(new SampleBusinessTask(), 'store-1', static function () use ($error) {
                throw $error;
            });
        });

        $entry = AdminAPI::get()->transactionLogs('store-1')->getTransactionLogs(1, 10)->toArray()['transactionLogs'][0];

        $keys = array_keys($entry);
        sort($keys);
        $expected = ['merchantReference', 'executionId', 'paymentMethod', 'timestamp', 'eventCode', 'isSuccessful',
            'queueStatus', 'reason', 'failureDescription', 'sequraLink', 'shopLink'];
        sort($expected);
        self::assertSame($expected, $keys);
        self::assertSame('failed', $entry['queueStatus']);
        self::assertSame('SampleBusinessTask', $entry['eventCode']);
        self::assertFalse($entry['isSuccessful']);
    }

    private function around(BusinessTaskInterface $job, string $storeId, callable $run): void
    {
        StoreContext::doWithStore($storeId, function () use ($job, $run) {
            $this->recorder->around($job, $run);
        });
    }

    private function expectSameException(Throwable $expected, callable $call): void
    {
        try {
            $call();
        } catch (Throwable $e) {
            self::assertSame($expected, $e);

            return;
        }

        self::fail('Expected exception');
    }

    private function orderJob(string $reference): TransactionalOrderUpdateTask
    {
        $data = new TransactionData($reference, 'shipped', (new DateTime('-1 hour'))->getTimestamp(), 'r', true);

        return new TransactionalOrderUpdateTask(MockOrderUpdateData::getOrderUpdateData(), $data);
    }

    private function scheduledOrderJob(): TransactionalOrderUpdateTask
    {
        $this->storeOrder('ref-1', 'store-1');
        $job = $this->orderJob('ref-1');
        StoreContext::doWithStore('store-1', function () use ($job) {
            $this->recorder->schedule($job);
        });

        return $job;
    }

    /**
     * @return string[]
     */
    private function eventCodes(string $storeId): array
    {
        return array_values(array_map(static function (TransactionLog $log) {
            return $log->getEventCode();
        }, $this->transactionLogRows($storeId)));
    }

    private function failedRow(string $eventCode, string $storeId): void
    {
        $this->rowAt($eventCode, (new DateTime('-1 hour'))->getTimestamp(), $storeId);
    }

    private function rowAt(string $eventCode, int $timestamp, string $storeId = 'store-7'): void
    {
        $service = TestServiceRegister::getService(TransactionLogService::class);
        StoreContext::doWithStore($storeId, static function () use ($service, $eventCode, $timestamp) {
            $service->createJobLog($eventCode, $timestamp, TransactionLog::FAILED);
        });
    }
}
