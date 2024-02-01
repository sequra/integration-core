<?php

namespace SeQura\Core\Tests\BusinessLogic\TransactionLog\Listeners;

use SeQura\Core\BusinessLogic\DataAccess\TransactionLog\Entities\TransactionLog;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidCartItemsException;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidQuantityException;
use SeQura\Core\BusinessLogic\TransactionLog\Listeners\FailedListener;
use SeQura\Core\BusinessLogic\TransactionLog\Models\TransactionData;
use SeQura\Core\BusinessLogic\TransactionLog\RepositoryContracts\TransactionLogRepositoryInterface;
use SeQura\Core\BusinessLogic\TransactionLog\Services\TransactionLogService;
use SeQura\Core\BusinessLogic\TransactionLog\Tasks\TransactionalOrderUpdateTask;
use SeQura\Core\BusinessLogic\Webhook\Services\ShopOrderService;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use SeQura\Core\Infrastructure\TaskExecution\Events\QueueItemFailedEvent;
use SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException;
use SeQura\Core\Infrastructure\TaskExecution\QueueItem;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\BusinessLogic\TransactionLog\Mocks\MockOrderUpdateData;
use SeQura\Core\Tests\BusinessLogic\WebhookAPI\MockComponents\MockShopOrderService;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class FailedListenerTest
 *
 * @package SeQura\Core\Tests\BusinessLogic\TransactionLog\Listeners
 */
class FailedListenerTest extends BaseTestCase
{
    /**
     * @var FailedListener
     */
    protected $listener;

    /**
     * @var TransactionData
     */
    private $transactionData;

    /**
     * @return void
     *
     * @throws RepositoryClassException
     */
    protected function setUp(): void
    {
        parent::setUp();

        TestServiceRegister::registerService(ShopOrderService::class, static function () {
            return new MockShopOrderService();
        });

        $this->listener = new FailedListener(TestServiceRegister::getService(TransactionLogService::class));
        $this->repository = TestServiceRegister::getService(TransactionLogRepositoryInterface::class);
        $this->transactionData = new TransactionData(
            '1',
            'code',
            123456789,
            'Partial refund',
            true
        );
    }

    /**
     * @throws QueueItemDeserializationException
     * @throws InvalidCartItemsException
     * @throws InvalidQuantityException
     */
    public function testAborted(): void
    {
        // arrange
        $log = new TransactionLog();
        $log->setExecutionId(1);
        $log->setIsSuccessful($this->transactionData->isSuccessful());
        $log->setReason($this->transactionData->getReason());
        $log->setEventCode($this->transactionData->getEventCode());
        $log->setPaymentMethod('Method');
        $log->setTimestamp($this->transactionData->getTimestamp());
        $log->setQueueStatus(QueueItem::ABORTED);
        $log->setMerchantReference($this->transactionData->getMerchantReference());
        $this->repository->setTransactionLog($log);

        $orderUpdateTask = new TransactionalOrderUpdateTask(MockOrderUpdateData::getOrderUpdateData(), $this->transactionData);
        $orderUpdateTask->setTransactionLog($log);
        $item = new QueueItem($orderUpdateTask);
        $item->setStatus(QueueItem::ABORTED);
        $event = new QueueItemFailedEvent($item, 'Failed!');

        // act
        $this->listener->handle($event);

        // assert
        self::assertEquals('Order update action not supported on SeQura.', $log->getFailureDescription());
    }
}
