<?php

namespace SeQura\Core\Tests\BusinessLogic\TaskExecution\Standalone;

use SeQura\Core\BusinessLogic\DataAccess\TransactionLog\Entities\TransactionLog;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Models\CountryConfiguration;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\RepositoryContracts\CountryConfigurationRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\TaskExecution\TaskExecutorInterface;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\Order\Models\SeQuraOrder;
use SeQura\Core\BusinessLogic\Domain\Order\Service\OrderService;
use SeQura\Core\BusinessLogic\Domain\OrderReport\OrderReporter;
use SeQura\Core\BusinessLogic\Domain\OrderReport\Service\OrderReportService;
use SeQura\Core\BusinessLogic\Domain\StatisticalData\Models\StatisticalData;
use SeQura\Core\BusinessLogic\Domain\StatisticalData\RepositoryContracts\StatisticalDataRepositoryInterface;
use SeQura\Core\BusinessLogic\TaskExecution\Standalone\LegacyStoredTaskUpgrader;
use SeQura\Core\BusinessLogic\TaskExecution\Standalone\TaskAdapter;
use SeQura\Core\BusinessLogic\TransactionLog\Models\TransactionData;
use SeQura\Core\BusinessLogic\TransactionLog\RepositoryContracts\TransactionLogRepositoryInterface;
use SeQura\Core\BusinessLogic\Webhook\Services\ShopOrderService;
use SeQura\Core\Infrastructure\ORM\RepositoryRegistry;
use SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException;
use SeQura\Core\Infrastructure\TaskExecution\Interfaces\StoredTaskUpgrader;
use SeQura\Core\Infrastructure\TaskExecution\QueueItem;
use SeQura\Core\Infrastructure\Utility\TimeProvider;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\BusinessLogic\TransactionLog\Mocks\MockOrderUpdateData;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution\FooTask;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;
use SeQura\Core\Infrastructure\Serializer\Serializer;

class LegacyStoredTaskUpgraderTest extends BaseTestCase
{
    private const LEGACY_REPORTER = '{"taskList":[],"page":1,"storeId":"store-7",'
    . '"class_name":"SeQura\\\\Core\\\\BusinessLogic\\\\Domain\\\\OrderReport\\\\OrderReporter"}';

    /**
     * @var int|null
     */
    private $timestamp;

    protected function setUp(): void
    {
        parent::setUp();

        TestServiceRegister::registerService(StoredTaskUpgrader::class, static function () {
            return new LegacyStoredTaskUpgrader(
                TestServiceRegister::getService(TransactionLogRepositoryInterface::class)
            );
        });
    }

    public function testLegacyOrderReporterBecomesTheNewJobInItsStoredStore(): void
    {
        $item = $this->itemWith(self::LEGACY_REPORTER, 'ctx-other');

        $task = $item->getTask();

        self::assertInstanceOf(TaskAdapter::class, $task);
        self::assertSame('OrderReporter', $task->getType());
        self::assertSame([
            'task_class' => OrderReporter::class,
            'task_data' => ['page' => 1],
            'context' => 'store-7',
        ], $task->toArray());
    }

    public function testLegacyOrderReporterKeepsItsStoredPageAndIgnoresTheTaskList(): void
    {
        $item = $this->itemWith(
            '{"taskList":[{"progress":0,"executionId":5,"weight":1}],"page":3,"storeId":"store-9",'
            . '"class_name":"SeQura\\\\Core\\\\BusinessLogic\\\\Domain\\\\OrderReport\\\\OrderReporter"}'
        );

        $task = $item->getTask();

        self::assertSame(['page' => 3], $task->toArray()['task_data']);
        self::assertSame('store-9', $task->toArray()['context']);
    }

    public function testLegacyOrderUpdateReusesTheTransactionLogRowOfTheQueueItem(): void
    {
        $item = $this->itemWith($this->legacyOrderUpdate(), 'ctx-other', 11);
        $rowId = $this->storeLog('store-1', 11);

        $data = $item->getTask()->toArray();

        self::assertSame('store-1', $data['context']);
        self::assertSame($rowId, $data['task_data']['transactionLogId']);
        self::assertArrayNotHasKey('storeId', $data['task_data']);
    }

    public function testLegacyOrderUpdateWithoutARowHasNoTransactionLogId(): void
    {
        $item = $this->itemWith($this->legacyOrderUpdate(), '', 11);

        $data = $item->getTask()->toArray();

        self::assertNull($data['task_data']['transactionLogId']);
        self::assertSame('store-1', $data['context']);
    }

    public function testLegacyOrderReporterRunsAsTheNewReportChainInItsStoredStore(): void
    {
        $shopOrderService = $this->createMock(ShopOrderService::class);
        $shopOrderService->method('getReportOrderIds')->with(1, 5000)->willReturn(['r1']);
        $shopOrderService->method('getStatisticsOrderIds')->willReturn([]);
        TestServiceRegister::registerService(ShopOrderService::class, static function () use ($shopOrderService) {
            return $shopOrderService;
        });
        $storesSeen = [];
        $reportService = $this->createMock(OrderReportService::class);
        $reportService->expects(self::once())->method('sendReport')->willReturnCallback(
            static function () use (&$storesSeen) {
                $storesSeen[] = StoreContext::getInstance()->getStoreId();

                return true;
            }
        );
        TestServiceRegister::registerService(OrderReportService::class, static function () use ($reportService) {
            return $reportService;
        });
        TestServiceRegister::registerService(TaskExecutorInterface::class, function () {
            return $this->createMock(TaskExecutorInterface::class);
        });
        StoreContext::doWithStore('store-7', static function () {
            TestServiceRegister::getService(StatisticalDataRepositoryInterface::class)
                ->setStatisticalData(new StatisticalData(true));
            TestServiceRegister::getService(CountryConfigurationRepositoryInterface::class)
                ->setCountryConfiguration([new CountryConfiguration('ES', 'testMerchantId')]);
        });

        $this->inProgress($this->itemWith(self::LEGACY_REPORTER, 'ctx-other'))->getTask()->execute();

        self::assertSame(['store-7'], $storesSeen);
    }

    public function testLegacyOrderUpdateRunsOnItsExistingTransactionLogRow(): void
    {
        $queueItemId = $this->inProgress($this->itemWith(Serializer::serialize(new FooTask())))->getId();
        $rowId = $this->storeLog('store-1', $queueItemId);
        $item = $this->inProgress($this->itemWith($this->legacyOrderUpdate(), 'ctx-other', $queueItemId));
        $observed = [];
        $this->registerOrderService($observed);

        $item->getTask()->execute();

        self::assertCount(1, $observed);
        self::assertSame('store-1', $observed[0]['store']);
        self::assertCount(1, $observed[0]['rows']);
        self::assertSame($rowId, $observed[0]['rows'][0]->getId());
        self::assertSame(TransactionLog::IN_PROGRESS, $observed[0]['rows'][0]->getQueueStatus());
        self::assertSame([], $this->logRows('store-1'));
    }

    public function testLegacyOrderUpdateWithoutARowCreatesOneWhileItRuns(): void
    {
        $item = $this->inProgress($this->itemWith($this->legacyOrderUpdate()));
        $observed = [];
        $this->registerOrderService($observed);

        $item->getTask()->execute();

        self::assertCount(1, $observed);
        self::assertCount(1, $observed[0]['rows']);
        self::assertSame(TransactionLog::IN_PROGRESS, $observed[0]['rows'][0]->getQueueStatus());
        self::assertSame([], $this->logRows('store-1'));
    }

    public function testItemsStoredBy7AreNotUpgraded(): void
    {
        $adapter = new TaskAdapter(new OrderReporter(2), 'store-7');
        $item = $this->itemWith(Serializer::serialize($adapter), 'store-7');

        self::assertNull((new LegacyStoredTaskUpgrader($this->logRepository()))->upgrade($item));
        self::assertSame(['page' => 2], $item->getTask()->toArray()['task_data']);
    }

    public function testPlatformTasksAreNotUpgraded(): void
    {
        $item = $this->itemWith(Serializer::serialize(new FooTask()));

        self::assertNull((new LegacyStoredTaskUpgrader($this->logRepository()))->upgrade($item));
        self::assertInstanceOf(FooTask::class, $item->getTask());
    }

    public function testLegacyBatchItemsFailCleanly(): void
    {
        $item = $this->itemWith(
            '{"storeId":"store-7","reportOrderIds":["r1"],"statisticsOrderIds":null,"merchantId":"m1",'
            . '"class_name":"SeQura\\\\Core\\\\BusinessLogic\\\\Domain\\\\OrderReport\\\\Tasks\\\\OrderReportTask"}'
        );

        $this->expectException(QueueItemDeserializationException::class);

        $item->getTask();
    }

    private function itemWith(string $serialized, string $context = '', ?int $id = null): QueueItem
    {
        $item = new QueueItem();
        $item->setSerializedTask($serialized);
        $item->setContext($context);
        if ($id !== null) {
            $item->setId($id);
        }

        return $item;
    }

    private function recentTimestamp(): int
    {
        if ($this->timestamp === null) {
            $this->timestamp = (int) TestServiceRegister::getService(TimeProvider::class)
                ->getCurrentLocalTime()->getTimestamp();
        }

        return $this->timestamp;
    }

    private function inProgress(QueueItem $item): QueueItem
    {
        $item->setStatus(QueueItem::IN_PROGRESS);
        $repository = RepositoryRegistry::getQueueItemRepository();
        if ($item->getId() === null) {
            $repository->save($item);
        } else {
            $repository->update($item);
        }

        return $item;
    }

    private function legacyOrderUpdate(): string
    {
        return json_encode([
            'orderUpdateData' => MockOrderUpdateData::getOrderUpdateData()->toArray(),
            'transactionData' => [
                'merchantReference' => 'ref-1',
                'eventCode' => 'ship',
                'timestamp' => $this->recentTimestamp(),
                'reason' => 'Update order',
                'isSuccessful' => true,
            ],
            'storeId' => 'store-1',
            'class_name' => 'SeQura\\Core\\BusinessLogic\\TransactionLog\\Tasks\\TransactionalOrderUpdateTask',
        ]);
    }

    private function storeLog(string $storeId, int $executionId): int
    {
        $repository = $this->logRepository();

        return StoreContext::doWithStore($storeId, function () use ($repository, $storeId, $executionId) {
            $log = new TransactionLog();
            $log->setStoreId($storeId);
            $log->setExecutionId($executionId);
            $log->setMerchantReference('ref-1');
            $log->setEventCode('ship');
            $log->setTimestamp($this->recentTimestamp());
            $log->setQueueStatus(TransactionLog::QUEUED);
            $repository->setTransactionLog($log);

            return $repository->getItemByExecutionId(
                $executionId,
                new TransactionData('ref-1', 'ship', $this->recentTimestamp(), 'Update order', true)
            )->getId();
        });
    }

    /**
     * @param array<int, array<string, mixed>> $observed
     */
    private function registerOrderService(array &$observed): void
    {
        $orderService = $this->createMock(OrderService::class);
        $orderService->expects(self::once())->method('updateOrder')->willReturnCallback(
            function () use (&$observed) {
                $observed[] = [
                    'store' => StoreContext::getInstance()->getStoreId(),
                    'rows' => $this->logRows('store-1'),
                ];

                return $this->createMock(SeQuraOrder::class);
            }
        );
        TestServiceRegister::registerService(OrderService::class, static function () use ($orderService) {
            return $orderService;
        });
    }

    /**
     * @return TransactionLog[]
     */
    private function logRows(string $storeId): array
    {
        $repository = $this->logRepository();

        return StoreContext::doWithStore($storeId, static function () use ($repository) {
            return $repository->find(100, 0);
        });
    }

    private function logRepository(): TransactionLogRepositoryInterface
    {
        return TestServiceRegister::getService(TransactionLogRepositoryInterface::class);
    }
}
