<?php

namespace SeQura\Core\BusinessLogic\TransactionLog\Services;

use DateTime;
use Exception;
use SeQura\Core\BusinessLogic\DataAccess\TransactionLog\Entities\TransactionLog;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\OrderNotFoundException;
use SeQura\Core\BusinessLogic\Domain\Order\Service\OrderService;
use SeQura\Core\BusinessLogic\TransactionLog\Contracts\TransactionLogAwareInterface;
use SeQura\Core\BusinessLogic\TransactionLog\RepositoryContracts\TransactionLogRepositoryInterface;
use SeQura\Core\BusinessLogic\Webhook\Services\ShopOrderService;
use SeQura\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;

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
     * @param TransactionLogAwareInterface $job
     * @param string $queueStatus
     *
     * @return TransactionLog
     *
     * @throws OrderNotFoundException
     */
    public function createOrderLog(TransactionLogAwareInterface $job, string $queueStatus): TransactionLog
    {
        $data = $job->getTransactionData();
        $order = $this->orderService->getOrderByShopReference($data->getMerchantReference());
        $paymentMethod = $order->getPaymentMethod() ? $order->getPaymentMethod()->getName() : '';

        $transactionLog = new TransactionLog();
        $transactionLog->setStoreId(StoreContext::getInstance()->getStoreId());
        $transactionLog->setMerchantReference($data->getMerchantReference());
        $transactionLog->setEventCode($data->getEventCode());
        $transactionLog->setReason($data->getReason());
        $transactionLog->setIsSuccessful($data->isSuccessful());
        $transactionLog->setTimestamp($data->getTimestamp());
        $transactionLog->setPaymentMethod($paymentMethod);
        $transactionLog->setQueueStatus($queueStatus);
        $transactionLog->setSequraLink(self::SEQURA_PORTAL_URL . urlencode($order->getReference()));
        $transactionLog->setShopLink($this->integrationOrderService->getOrderUrl($data->getMerchantReference()));

        $this->saveWithOwnExecutionId($transactionLog);

        return $transactionLog;
    }

    /**
     * @param string $eventCode
     * @param int $timestamp
     * @param string $queueStatus
     *
     * @return TransactionLog
     */
    public function createJobLog(string $eventCode, int $timestamp, string $queueStatus): TransactionLog
    {
        $transactionLog = new TransactionLog();
        $transactionLog->setStoreId(StoreContext::getInstance()->getStoreId());
        $transactionLog->setMerchantReference('');
        $transactionLog->setEventCode($eventCode);
        $transactionLog->setReason('');
        $transactionLog->setIsSuccessful(false);
        $transactionLog->setTimestamp($timestamp);
        $transactionLog->setPaymentMethod('');
        $transactionLog->setQueueStatus($queueStatus);
        $transactionLog->setSequraLink('');
        $transactionLog->setShopLink('');
        $transactionLog->setFailureDescription('');

        $this->saveWithOwnExecutionId($transactionLog);

        return $transactionLog;
    }

    /**
     * @param int $id
     *
     * @return TransactionLog|null
     */
    public function findById(int $id): ?TransactionLog
    {
        return $this->transactionLogRepository->getItemById($id);
    }

    /**
     * @param string $eventCode
     *
     * @return void
     *
     * @throws QueryFilterInvalidParamException
     */
    public function deleteFailedWithoutOrder(string $eventCode): void
    {
        foreach ($this->transactionLogRepository->findWithoutOrder($eventCode, TransactionLog::FAILED) as $log) {
            $this->delete($log->getId());
        }
    }

    /**
     * @param TransactionLog $transactionLog
     *
     * @return void
     */
    private function saveWithOwnExecutionId(TransactionLog $transactionLog): void
    {
        $transactionLog->setExecutionId(0);
        $this->save($transactionLog);
        $transactionLog->setExecutionId($transactionLog->getId());
        $this->update($transactionLog);
    }
}
