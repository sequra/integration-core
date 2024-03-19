<?php

namespace SeQura\Core\Tests\BusinessLogic\TransactionLog\Listeners;

use Exception;
use SeQura\Core\BusinessLogic\DataAccess\TransactionLog\Entities\TransactionLog;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\Order\Models\SeQuraOrder;
use SeQura\Core\BusinessLogic\Domain\Order\RepositoryContracts\SeQuraOrderRepositoryInterface;
use SeQura\Core\BusinessLogic\TransactionLog\Listeners\CreateListener;
use SeQura\Core\BusinessLogic\TransactionLog\Models\TransactionData;
use SeQura\Core\BusinessLogic\TransactionLog\RepositoryContracts\TransactionLogRepositoryInterface;
use SeQura\Core\BusinessLogic\TransactionLog\Services\TransactionLogService;
use SeQura\Core\BusinessLogic\TransactionLog\Tasks\TransactionalOrderUpdateTask;
use SeQura\Core\BusinessLogic\Webhook\Services\ShopOrderService;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use SeQura\Core\Infrastructure\TaskExecution\Events\QueueItemEnqueuedEvent;
use SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException;
use SeQura\Core\Infrastructure\TaskExecution\QueueItem;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\BusinessLogic\TransactionLog\Mocks\MockOrderUpdateData;
use SeQura\Core\Tests\BusinessLogic\WebhookAPI\MockComponents\MockShopOrderService;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TaskExecution\FooTask;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class CreateListenerTest
 *
 * @package SeQura\Core\Tests\BusinessLogic\TransactionLog\Listeners
 */
class CreateListenerTest extends BaseTestCase
{
    /**
     * @var CreateListener
     */
    protected $listener;

    /**
     * @var TransactionData
     */
    private $transactionData;

    /**
     * @var TransactionLogRepositoryInterface
     */
    private $repository;

    /**
     * @var SeQuraOrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @return void
     * @throws RepositoryClassException
     */
    protected function setUp(): void
    {
        parent::setUp();

        TestServiceRegister::registerService(ShopOrderService::class, static function () {
            return new MockShopOrderService();
        });

        $this->listener = new CreateListener(TestServiceRegister::getService(TransactionLogService::class));
        $this->repository = TestServiceRegister::getService(TransactionLogRepositoryInterface::class);
        $this->orderRepository = TestServiceRegister::getService(SeQuraOrderRepositoryInterface::class);
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
     */
    public function testCreateInvalidTask(): void
    {
        // arrange
        $item = new QueueItem(new FooTask());
        $item->setStatus(QueueItem::QUEUED);
        $event = new QueueItemEnqueuedEvent($item);

        // act
        $this->listener->handle($event);

        // assert
        self::assertNull($this->repository->getTransactionLog('1'));
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testCreateSuccess(): void
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

        $log = new TransactionLog();
        $log->setExecutionId(1);
        $log->setIsSuccessful($this->transactionData->isSuccessful());
        $log->setReason($this->transactionData->getReason());
        $log->setEventCode($this->transactionData->getEventCode());
        $log->setPaymentMethod('Method');
        $log->setTimestamp($this->transactionData->getTimestamp());
        $log->setQueueStatus(QueueItem::ABORTED);
        $log->setMerchantReference($this->transactionData->getMerchantReference());
        $orderUpdateTask = new TransactionalOrderUpdateTask(MockOrderUpdateData::getOrderUpdateData(), $this->transactionData);
        $item = new QueueItem($orderUpdateTask);
        $item->setId(1);
        $item->setStatus(QueueItem::QUEUED);
        $event = new QueueItemEnqueuedEvent($item);

        // act
        $this->listener->handle($event);

        // assert
        $log = $this->repository->getTransactionLog('ZXCV1234');
        self::assertNotNull($log);
        self::assertEquals(QueueItem::QUEUED, $log->getQueueStatus());
    }
}
