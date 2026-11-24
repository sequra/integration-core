<?php

namespace SeQura\Core\BusinessLogic\TransactionLog\Tasks;

use SeQura\Core\BusinessLogic\Domain\Integration\TaskExecution\BusinessTaskInterface;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderUpdateData;
use SeQura\Core\BusinessLogic\Domain\Order\Service\OrderService;
use SeQura\Core\BusinessLogic\TransactionLog\Models\TransactionData;
use SeQura\Core\Infrastructure\ServiceRegister;

/**
 * Class TransactionalOrderUpdateTask
 *
 * @package SeQura\Core\BusinessLogic\TransactionLog\Tasks
 *
 * @phpstan-consistent-constructor
 */
class TransactionalOrderUpdateTask extends TransactionalTask
{
    /**
     * @var OrderUpdateData
     */
    protected $orderUpdateData;

    /**
     * @var TransactionData
     */
    protected $transactionData;

    /**
     * @param OrderUpdateData $orderUpdateData
     * @param TransactionData $transactionData
     */
    public function __construct(OrderUpdateData $orderUpdateData, TransactionData $transactionData)
    {
        $this->orderUpdateData = $orderUpdateData;
        $this->transactionData = $transactionData;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $this->getOrderService()->updateOrder($this->orderUpdateData);
        $this->transactionData->setIsSuccessful(true);

        yield 100;
    }

    /**
     * @return OrderService
     */
    protected function getOrderService(): OrderService
    {
        return ServiceRegister::getService(OrderService::class);
    }

    /**
     * @inheritDoc
     */
    public static function fromArray(array $data): BusinessTaskInterface
    {
        $task = new static(
            OrderUpdateData::fromArray($data['orderUpdateData']),
            TransactionData::fromArray($data['transactionData'])
        );

        if (isset($data['transactionLogId'])) {
            $task->setTransactionLogId((int)$data['transactionLogId']);
        }

        return $task;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            'orderUpdateData' => $this->orderUpdateData->toArray(),
            'transactionData' => $this->transactionData->toArray(),
            'transactionLogId' => $this->transactionLogId,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getTransactionData(): TransactionData
    {
        return $this->transactionData;
    }

    /**
     * Gets the OrderUpdateData.
     *
     * @return OrderUpdateData
     */
    public function getOrderUpdateData(): OrderUpdateData
    {
        return $this->orderUpdateData;
    }
}
