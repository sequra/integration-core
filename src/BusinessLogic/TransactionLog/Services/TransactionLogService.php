<?php

namespace SeQura\Core\BusinessLogic\TransactionLog\Services;

use DateTime;
use Exception;
use SeQura\Core\BusinessLogic\DataAccess\TransactionLog\Entities\TransactionLog;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\OrderNotFoundException;
use SeQura\Core\BusinessLogic\Domain\Order\Service\OrderService;
use SeQura\Core\BusinessLogic\TransactionLog\Contracts\TransactionLogAwareInterface;
use SeQura\Core\BusinessLogic\TransactionLog\RepositoryContracts\TransactionLogRepositoryInterface;
use SeQura\Core\BusinessLogic\Webhook\Services\ShopOrderService;
use SeQura\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException;
use SeQura\Core\Infrastructure\TaskExecution\QueueItem;
use SeQura\Core\Infrastructure\TaskExecution\Task;

/**
 * Class TransactionLogService
 *
 * @package SeQura\Core\BusinessLogic\TransactionLog\Services
 */
class TransactionLogService
{
    /**
    * @var string Base URL for sequra portal
    */
    public const SEQURA_PORTAL_URL = 'https://simbox.sequrapi.com/orders/';

    /**
     * @var TransactionLogRepositoryInterface
     */
    protected $transactionLogRepository;
    /**
     * @var OrderService
     */
    protected $orderService;
    /**
     * @var ShopOrderService
     */
    protected $integrationOrderService;

    public function __construct(
        TransactionLogRepositoryInterface $transactionLogRepository,
        OrderService $orderService,
        ShopOrderService $integrationOrderService
    ) {
        $this->transactionLogRepository = $transactionLogRepository;
        $this->orderService = $orderService;
        $this->integrationOrderService = $integrationOrderService;
    }

    /**
     * Creates transaction log. Fails existing.
     *
     * @param QueueItem $item
     *
     * @return void
     *
     * @throws QueueItemDeserializationException
     * @throws OrderNotFoundException
     */
    public function create(QueueItem $item): void
    {
        /**
 * @var Task | TransactionLogAwareInterface $task
*/
        $task = $item->getTask();
        if ($task === null) {
            return;
        }

        if (!($task instanceof TransactionLogAwareInterface)) {
            return;
        }

        if ($item->getParentId() !== null) {
            return;
        }

        if ($item->getId() && ($log = $this->transactionLogRepository->getItemByExecutionId($item->getId())) !== null) {
            $log->setQueueStatus(QueueItem::FAILED);
            $this->update($log);
        }

        $transactionLog = $this->createTransactionLogInstance($item);
        $this->save($transactionLog);

        $task->setTransactionLog($transactionLog);
    }

    /**
     * Saves transaction log.
     *
     * @param TransactionLog $transactionLog
     *
     * @return void
     */
    public function save(TransactionLog $transactionLog): void
    {
        $this->transactionLogRepository->setTransactionLog($transactionLog);
    }

    /**
     * Saves transaction log.
     *
     * @param TransactionLog $transactionLog
     *
     * @return void
     */
    public function update(TransactionLog $transactionLog): void
    {
        $this->transactionLogRepository->updateTransactionLog($transactionLog);
    }

    /**
     * Delete transaction log.
     *
     * @param int $transactionLogId
     *
     * @return void
     *
     * @throws QueryFilterInvalidParamException
     */
    public function delete(int $transactionLogId): void
    {
        $this->transactionLogRepository->deleteTransactionLogById($transactionLogId);
    }

    /**
     * Loads transaction log.
     *
     * @param QueueItem $item
     *
     * @return void
     *
     * @throws QueueItemDeserializationException
     */
    public function load(QueueItem $item): void
    {
        /**
        * @var TransactionLogAwareInterface $task
        */
        $task = $item->getTask();

        if ($task === null) {
            return;
        }

        $id = $item->getParentId() ?? $item->getId();
        $log = $this->transactionLogRepository->getItemByExecutionId($id);
        if ($log) {
            $task->setTransactionLog($log);
        }
    }

    /**
     * @param QueueItem $queueItem
     *
     * @return bool
     */
    public function hasQueueItem(QueueItem $queueItem): bool
    {
        return !($this->transactionLogRepository->getItemByExecutionId($queueItem->getId()) === null);
    }

    /**
     * @param int $limit
     * @param int $offset
     *
     * @return TransactionLog[]
     *
     * @throws Exception
     */
    public function find(int $limit, int $offset): array
    {
        return $this->transactionLogRepository->find($limit, $offset);
    }

    /**
     * @param string $merchantReference
     *
     * @return TransactionLog|null
     *
     * @throws Exception
     */
    public function findByMerchantReference(string $merchantReference): ?TransactionLog
    {
        return $this->transactionLogRepository->findByMerchantReference($merchantReference);
    }

    /**
     * @param int $page
     * @param int $limit
     *
     * @return bool
     *
     * @throws Exception
     */
    public function hasNextPage(int $page, int $limit): bool
    {
        $count = $this->transactionLogRepository->count();

        if ($page <= 1) {
            return $limit < $count;
        }

        return $page * $limit < $count;
    }

    /**
     * @param DateTime $beforeDate
     * @param int $limit
     *
     * @return void
     *
     * @throws Exception
     */
    public function deleteLogs(DateTime $beforeDate, int $limit): void
    {
        $this->transactionLogRepository->deleteLogs($beforeDate, $limit);
    }

    /**
     * @param DateTime $beforeDate
     *
     * @return bool
     *
     * @throws Exception
     */
    public function logsExist(DateTime $beforeDate): bool
    {
        return $this->transactionLogRepository->logsExist($beforeDate);
    }

    /**
     * @param QueueItem $item
     *
     * @return TransactionLog
     *
     * @throws QueueItemDeserializationException
     * @throws OrderNotFoundException
     */
    protected function createTransactionLogInstance(QueueItem $item): TransactionLog
    {

        /**
        * @var TransactionLogAwareInterface $task
        */
        $task = $item->getTask();
        $order = $this->orderService->getOrderByShopReference($task->getTransactionData()->getMerchantReference());
        $paymentMethod = '';
        if ($order->getPaymentMethod()) {
            $paymentMethod = $order->getPaymentMethod()->getName();
        }

        $transactionLog = new TransactionLog();
        $transactionLog->setStoreId($task->getStoreId() ?? '');
        $transactionLog->setMerchantReference($task->getTransactionData()->getMerchantReference());
        $transactionLog->setExecutionId($item->getId() ?? 0);
        $transactionLog->setEventCode($task->getTransactionData()->getEventCode());
        $transactionLog->setReason($task->getTransactionData()->getReason());
        $transactionLog->setIsSuccessful($task->getTransactionData()->isSuccessful());
        $transactionLog->setTimestamp($task->getTransactionData()->getTimestamp());
        $transactionLog->setPaymentMethod($paymentMethod);
        $transactionLog->setQueueStatus(QueueItem::QUEUED);
        $transactionLog->setSequraLink(self::SEQURA_PORTAL_URL . urlencode($order->getReference()));
        $transactionLog->setShopLink(
            $this->integrationOrderService->getOrderUrl($task->getTransactionData()->getMerchantReference())
        );

        return $transactionLog;
    }
}
