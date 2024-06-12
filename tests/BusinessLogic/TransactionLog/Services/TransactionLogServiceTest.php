<?php

namespace SeQura\Core\Tests\BusinessLogic\TransactionLog\Services;

use Exception;
use SeQura\Core\BusinessLogic\DataAccess\TransactionLog\Entities\TransactionLog;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidCartItemsException;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidQuantityException;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\OrderNotFoundException;
use SeQura\Core\BusinessLogic\Domain\Order\Models\SeQuraOrder;
use SeQura\Core\BusinessLogic\Domain\Order\RepositoryContracts\SeQuraOrderRepositoryInterface;
use SeQura\Core\BusinessLogic\TransactionLog\Models\TransactionData;
use SeQura\Core\BusinessLogic\TransactionLog\RepositoryContracts\TransactionLogRepositoryInterface;
use SeQura\Core\BusinessLogic\TransactionLog\Services\TransactionLogService;
use SeQura\Core\BusinessLogic\TransactionLog\Tasks\TransactionalOrderUpdateTask;
use SeQura\Core\BusinessLogic\Webhook\Services\ShopOrderService;
use SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException;
use SeQura\Core\Infrastructure\TaskExecution\QueueItem;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\BusinessLogic\TransactionLog\Mocks\MockOrderUpdateData;
use SeQura\Core\Tests\BusinessLogic\TransactionLog\Mocks\MockTransactionLogRepository;
use SeQura\Core\Tests\BusinessLogic\WebhookAPI\MockComponents\MockShopOrderService;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution\FooTask;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class TransactionLogServiceTest
 *
 * @package SeQura\Core\Tests\BusinessLogic\TransactionLog\Services
 */
class TransactionLogServiceTest extends BaseTestCase
{
    /**
     * @var TransactionLogRepositoryInterface
     */
    private $repository;

    /**
     * @var SeQuraOrderRepositoryInterface
     */
    private $orderRepository;

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

        $this->repository = new MockTransactionLogRepository();
        TestServiceRegister::registerService(
            TransactionLogRepositoryInterface::class,
            function () {
                return $this->repository;
            }
        );

        $this->orderRepository = TestServiceRegister::getService(SeQuraOrderRepositoryInterface::class);
        $this->service = TestServiceRegister::getService(TransactionLogService::class);
        $this->transactionData = new TransactionData(
            'ZXCV1234',
            'code',
            123456789,
            'Partial refund',
            true
        );
    }

    /**
     * @return void
     *
     * @throws QueueItemDeserializationException
     * @throws OrderNotFoundException
     */
    public function testCreateTaskNonTransactional(): void
    {
        // arrange
        $item = new QueueItem(new FooTask());

        // act
        $this->service->create($item);

        // assert
        self::assertNull($this->repository->getTransactionLog('1'));
    }

    /**
     * @throws Exception
     */
    public function testCreate(): void
    {
        // arrange
        $order = file_get_contents(__DIR__ . '/../../Common/MockObjects/SeQuraOrder.json');
        $array = json_decode($order, true);
        $seQuraOrder = SeQuraOrder::fromArray($array['order']);
        $seQuraOrder->setReference('sequra-ref-1234');
        $seQuraOrder->setCartId('5678');
        $seQuraOrder->setOrderRef1('ZXCV1234');
        $seQuraOrder->setState('approved');
        StoreContext::doWithStore('1', [$this->orderRepository, 'setSeQuraOrder'], [$seQuraOrder]);

        $task = new TransactionalOrderUpdateTask(MockOrderUpdateData::getOrderUpdateData(), $this->transactionData);
        $item = new QueueItem($task);

        // act
        $this->service->create($item);

        // assert
        $log = $this->repository->getTransactionLog('ZXCV1234');
        self::assertEquals($this->transactionData->getTimestamp(), $log->getTimestamp());
        self::assertEquals($this->transactionData->getMerchantReference(), $log->getMerchantReference());
        self::assertEquals($this->transactionData->getReason(), $log->getReason());
        self::assertEquals($this->transactionData->getEventCode(), $log->getEventCode());
        self::assertEquals($this->transactionData->isSuccessful(), $log->isSuccessful());
        self::assertEquals(QueueItem::QUEUED, $log->getQueueStatus());
        self::assertNull($log->getFailureDescription());
        self::assertEquals($task->getTransactionLog(), $log);
    }

    /**
     * @throws Exception
     */
    public function testUpdate(): void
    {
        // arrange
        $order = file_get_contents(__DIR__ . '/../../Common/MockObjects/SeQuraOrder.json');
        $array = json_decode($order, true);
        $seQuraOrder = SeQuraOrder::fromArray($array['order']);
        $seQuraOrder->setReference('sequra-ref-1234');
        $seQuraOrder->setCartId('5678');
        $seQuraOrder->setOrderRef1('ZXCV1234');
        $seQuraOrder->setState('approved');
        StoreContext::doWithStore('1', [$this->orderRepository, 'setSeQuraOrder'], [$seQuraOrder]);

        $task = new TransactionalOrderUpdateTask(MockOrderUpdateData::getOrderUpdateData(), $this->transactionData);
        $item = new QueueItem($task);

        // act
        $this->service->create($item);
        $log = $this->repository->getTransactionLog('ZXCV1234');
        $log->setQueueStatus(QueueItem::FAILED);
        $log->setFailureDescription('FAILURE');
        $this->service->save($log);

        // assert
        $log = $this->repository->getTransactionLog('ZXCV1234');
        self::assertEquals($this->transactionData->getTimestamp(), $log->getTimestamp());
        self::assertEquals($this->transactionData->getMerchantReference(), $log->getMerchantReference());
        self::assertEquals($this->transactionData->getReason(), $log->getReason());
        self::assertEquals($this->transactionData->getEventCode(), $log->getEventCode());
        self::assertEquals($this->transactionData-> isSuccessful(), $log->isSuccessful());
        self::assertEquals(QueueItem::FAILED, $log->getQueueStatus());
        self::assertEquals('FAILURE', $log->getFailureDescription());
        self::assertEquals($task->getTransactionLog(), $log);
    }

    /**
     * @throws QueueItemDeserializationException
     * @throws InvalidCartItemsException
     * @throws InvalidQuantityException
     */
    public function testLoad(): void
    {
        // arrange
        $task = new TransactionalOrderUpdateTask(MockOrderUpdateData::getOrderUpdateData(), $this->transactionData);
        $task->setExecutionId(1);
        $log = new TransactionLog();
        $log->setExecutionId(1);
        $log->setIsSuccessful($this->transactionData->isSuccessful());
        $log->setReason($this->transactionData->getReason());
        $log->setEventCode($this->transactionData->getEventCode());
        $log->setPaymentMethod('Method');
        $log->setTimestamp($this->transactionData->getTimestamp());
        $log->setQueueStatus(QueueItem::QUEUED);
        $log->setMerchantReference($this->transactionData->getMerchantReference());
        $this->repository->setTransactionLog($log);
        $item = new QueueItem($task);
        $item->setId(1);

        // act
        $this->service->load($item);

        // assert
        self::assertEquals($task->getTransactionLog(), $log);
        self::assertEquals($this->transactionData->getTimestamp(), $task->getTransactionLog()->getTimestamp());
        self::assertEquals($this->transactionData->getMerchantReference(), $task->getTransactionLog()->getMerchantReference());
        self::assertEquals($this->transactionData->getReason(), $task->getTransactionLog()->getReason());
        self::assertEquals($this->transactionData->getEventCode(), $task->getTransactionLog()->getEventCode());
        self::assertEquals($this->transactionData->isSuccessful(), $task->getTransactionLog()->isSuccessful());
        self::assertEquals(QueueItem::QUEUED, $task->getTransactionLog()->getQueueStatus());
        self::assertNull($log->getFailureDescription());
    }
}
