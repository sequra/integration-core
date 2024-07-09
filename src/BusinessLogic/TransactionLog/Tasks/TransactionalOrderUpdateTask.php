<?php

namespace SeQura\Core\BusinessLogic\TransactionLog\Tasks;

use Exception;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderUpdateData;
use SeQura\Core\BusinessLogic\Domain\Order\Service\OrderService;
use SeQura\Core\BusinessLogic\TransactionLog\Models\TransactionData;
use SeQura\Core\Infrastructure\Serializer\Serializer;
use SeQura\Core\Infrastructure\ServiceRegister;

/**
 * Class TransactionalOrderUpdateTask
 *
 * @package SeQura\Core\BusinessLogic\TransactionLog\Tasks
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
     * @var string
     */
    protected $storeId;

    /**
     * @param OrderUpdateData $orderUpdateData
     * @param TransactionData $transactionData
     */
    public function __construct(OrderUpdateData $orderUpdateData, TransactionData $transactionData)
    {
        $this->orderUpdateData = $orderUpdateData;
        $this->transactionData = $transactionData;
        $this->storeId = StoreContext::getInstance()->getStoreId();
    }

    /**
     * @inheritDoc
     *
     * @throws Exception
     */
    public function execute(): void
    {
        StoreContext::doWithStore(
            $this->storeId,
            function () {
                $this->doExecute();
            }
        );
    }

    /**
     * Executes task behavior.
     *
     * @return void
     *
     * @throws Exception
     */
    protected function doExecute(): void
    {
        $this->getOrderService()->updateOrder($this->orderUpdateData);
        $this->transactionData->setIsSuccessful(true);
        $this->reportProgress(100);
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
     *
     * @throws Exception
     */
    public static function fromArray(array $array): TransactionalOrderUpdateTask
    {
        return StoreContext::doWithStore($array['storeId'], static function () use ($array) {
            return new static(
                OrderUpdateData::fromArray($array['orderUpdateData']),
                TransactionData::fromArray($array['transactionData'])
            );
        });
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            'orderUpdateData' => $this->orderUpdateData->toArray(),
            'transactionData' => $this->transactionData->toArray(),
            'storeId' => $this->storeId,
        ];
    }

    /**
     * @inheritDoc
     */
    public function __serialize(): array
    {
        return $this->toArray();
    }

    /**
     * @inheritDoc
     *
     * @throws Exception
     */
    public function __unserialize($data): void
    {
        $this->storeId = $data['storeId'];
        $this->orderUpdateData = OrderUpdateData::fromArray($data['orderUpdateData']);
        $this->transactionData = TransactionData::fromArray($data['transactionData']);
    }

    /**
     * String representation of object
     *
     * @return string the string representation of the object or null
     */
    public function serialize(): string
    {
        return Serializer::serialize(array($this->orderUpdateData, $this->transactionData, $this->storeId));
    }

    /**
     * Constructs the object
     *
     * @param string $serialized <p>
     * The string representation of the object.
     * </p>
     *
     * @return void
     */
    public function unserialize($serialized): void
    {
        [$this->orderUpdateData, $this->transactionData, $this->storeId] = Serializer::unserialize($serialized);
    }

    /**
     * @inheritDoc
     */
    public function getStoreId(): string
    {
        return $this->storeId;
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
