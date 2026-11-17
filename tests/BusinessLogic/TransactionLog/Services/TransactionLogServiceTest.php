<?php

namespace SeQura\Core\Tests\BusinessLogic\TransactionLog\Services;

use SeQura\Core\BusinessLogic\DataAccess\TransactionLog\Entities\TransactionLog;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\OrderNotFoundException;
use SeQura\Core\BusinessLogic\TransactionLog\Models\TransactionData;
use SeQura\Core\BusinessLogic\TransactionLog\RepositoryContracts\TransactionLogRepositoryInterface;
use SeQura\Core\BusinessLogic\TransactionLog\Services\TransactionLogService;
use SeQura\Core\BusinessLogic\TransactionLog\Tasks\TransactionalOrderUpdateTask;
use SeQura\Core\BusinessLogic\Webhook\Services\ShopOrderService;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\BusinessLogic\TransactionLog\Mocks\MockOrderUpdateData;
use SeQura\Core\Tests\BusinessLogic\TransactionLog\Mocks\TransactionLogFixtures;
use SeQura\Core\Tests\BusinessLogic\WebhookAPI\MockComponents\MockShopOrderService;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

class TransactionLogServiceTest extends BaseTestCase
{
    use TransactionLogFixtures;

    /**
     * @var TransactionLogRepositoryInterface
     */
    private $repository;

    /**
     * @var TransactionLogService
     */
    private $service;

    /**
     * @var TransactionData
     */
    private $transactionData;

    protected function setUp(): void
    {
        parent::setUp();

        TestServiceRegister::registerService(ShopOrderService::class, static function () {
            return new MockShopOrderService();
        });

        $this->repository = TestServiceRegister::getService(TransactionLogRepositoryInterface::class);
        $this->service = TestServiceRegister::getService(TransactionLogService::class);
        $this->transactionData = new TransactionData('ZXCV1234', 'code', 123456789, 'Partial refund', true);
    }

    public function testCreateOrderLogBuildsTheRowFromTheOrder(): void
    {
        $this->storeOrder('ZXCV1234', '1');
        $task = new TransactionalOrderUpdateTask(MockOrderUpdateData::getOrderUpdateData(), $this->transactionData);

        $log = StoreContext::doWithStore('1', function () use ($task) {
            return $this->service->createOrderLog($task, TransactionLog::QUEUED);
        });

        $stored = StoreContext::doWithStore('1', function () {
            return $this->repository->getTransactionLog('ZXCV1234');
        });
        self::assertNotNull($stored);
        self::assertSame($log->getId(), $stored->getId());
        self::assertSame($stored->getId(), $stored->getExecutionId());
        self::assertSame('1', $stored->getStoreId());
        self::assertSame('code', $stored->getEventCode());
        self::assertSame(123456789, $stored->getTimestamp());
        self::assertSame('Partial refund', $stored->getReason());
        self::assertSame(TransactionLog::QUEUED, $stored->getQueueStatus());
        self::assertSame(TransactionLogService::SEQURA_PORTAL_URL . 'sequra-ref-1234', $stored->getSequraLink());
        self::assertNull($stored->getFailureDescription());
    }

    public function testCreateOrderLogFailsForUnknownOrder(): void
    {
        $task = new TransactionalOrderUpdateTask(MockOrderUpdateData::getOrderUpdateData(), $this->transactionData);

        $this->expectException(OrderNotFoundException::class);

        StoreContext::doWithStore('1', function () use ($task) {
            $this->service->createOrderLog($task, TransactionLog::QUEUED);
        });
    }

    public function testCreateJobLogLeavesTheOrderFieldsEmpty(): void
    {
        $log = StoreContext::doWithStore('store-7', function () {
            return $this->service->createJobLog('SomeJob', 55, TransactionLog::IN_PROGRESS);
        });

        $found = StoreContext::doWithStore('store-7', function () use ($log) {
            return $this->service->findById($log->getId());
        });
        self::assertNotNull($found);
        self::assertSame($found->getId(), $found->getExecutionId());
        self::assertSame('SomeJob', $found->getEventCode());
        self::assertSame('store-7', $found->getStoreId());
        self::assertSame(55, $found->getTimestamp());
        self::assertSame(TransactionLog::IN_PROGRESS, $found->getQueueStatus());
        self::assertFalse($found->isSuccessful());
        self::assertOrderFieldsEmpty($found);
    }

    public function testFindByIdIgnoresRowsOfOtherStores(): void
    {
        $log = StoreContext::doWithStore('store-7', function () {
            return $this->service->createJobLog('SomeJob', 55, TransactionLog::IN_PROGRESS);
        });

        $found = StoreContext::doWithStore('store-9', function () use ($log) {
            return $this->service->findById($log->getId());
        });

        self::assertNull($found);
    }

    public function testFindByIdMatchesThePrimaryKeyNotTheExecutionId(): void
    {
        $log = StoreContext::doWithStore('store-7', function () {
            return $this->service->createJobLog('SomeJob', 55, TransactionLog::IN_PROGRESS);
        });
        $log->setExecutionId($log->getId() + 1000);
        StoreContext::doWithStore('store-7', function () use ($log) {
            $this->service->update($log);
        });

        $found = StoreContext::doWithStore('store-7', function () use ($log) {
            return $this->service->findById($log->getId());
        });

        self::assertNotNull($found);
        self::assertSame($log->getId(), $found->getId());
    }

    public function testFindByIdReturnsNullForUnknownId(): void
    {
        self::assertNull(StoreContext::doWithStore('1', function () {
            return $this->service->findById(999);
        }));
    }

    public function testDeleteFailedWithoutOrderOnlyRemovesMatchingRowsOfTheCurrentStore(): void
    {
        StoreContext::doWithStore('store-7', function () {
            $this->service->createJobLog('SomeJob', 55, TransactionLog::FAILED);
            $this->service->createJobLog('SomeJob', 55, TransactionLog::IN_PROGRESS);
            $this->service->createJobLog('OtherJob', 55, TransactionLog::FAILED);
            $this->service->deleteFailedWithoutOrder('SomeJob');
        });

        $remaining = StoreContext::doWithStore('store-7', function () {
            return $this->service->find(10, 0);
        });
        $summary = array_map(static function (TransactionLog $log) {
            return $log->getEventCode() . ':' . $log->getQueueStatus();
        }, $remaining);
        sort($summary);
        self::assertSame(['OtherJob:failed', 'SomeJob:in_progress'], $summary);
    }
}
