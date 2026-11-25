<?php

namespace SeQura\Core\BusinessLogic\TaskExecution\Standalone;

use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderUpdateData;
use SeQura\Core\BusinessLogic\Domain\OrderReport\OrderReporter;
use SeQura\Core\BusinessLogic\TransactionLog\Models\TransactionData;
use SeQura\Core\BusinessLogic\TransactionLog\RepositoryContracts\TransactionLogRepositoryInterface;
use SeQura\Core\BusinessLogic\TransactionLog\Tasks\TransactionalOrderUpdateTask;
use SeQura\Core\Infrastructure\TaskExecution\Interfaces\StoredTaskUpgrader;
use SeQura\Core\Infrastructure\TaskExecution\QueueItem;
use SeQura\Core\Infrastructure\TaskExecution\Task;

class LegacyStoredTaskUpgrader implements StoredTaskUpgrader
{
    /**
     * @var TransactionLogRepositoryInterface
     */
    private $transactionLogRepository;

    public function __construct(TransactionLogRepositoryInterface $transactionLogRepository)
    {
        $this->transactionLogRepository = $transactionLogRepository;
    }

    public function upgrade(QueueItem $item): ?Task
    {
        $stored = json_decode((string) $item->getSerializedTask(), true);
        if (
            !\is_array($stored)
            || !isset($stored['class_name'], $stored['storeId'])
            || !\is_string($stored['storeId'])
        ) {
            return null;
        }

        switch ($stored['class_name']) {
            case OrderReporter::class:
                return new TaskAdapter(new OrderReporter((int) ($stored['page'] ?? 1)), $stored['storeId']);
            case TransactionalOrderUpdateTask::class:
                return $this->upgradeOrderUpdate($item, $stored);
            default:
                return null;
        }
    }

    /**
     * @param QueueItem $item
     * @param array<string, mixed> $stored
     *
     * @return Task
     */
    private function upgradeOrderUpdate(QueueItem $item, array $stored): Task
    {
        $transactionData = TransactionData::fromArray($stored['transactionData']);
        $job = new TransactionalOrderUpdateTask(
            OrderUpdateData::fromArray($stored['orderUpdateData']),
            $transactionData
        );

        $storeId = $stored['storeId'];
        $queueItemId = $item->getId();
        $log = $queueItemId === null ? null : StoreContext::doWithStore($storeId, function () use ($queueItemId, $transactionData) {
            return $this->transactionLogRepository->getItemByExecutionId($queueItemId, $transactionData);
        });

        if ($log !== null) {
            $job->setTransactionLogId($log->getId());
        }

        return new TaskAdapter($job, $storeId);
    }
}
