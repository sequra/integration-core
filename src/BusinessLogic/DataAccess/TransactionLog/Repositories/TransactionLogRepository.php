<?php

namespace SeQura\Core\BusinessLogic\DataAccess\TransactionLog\Repositories;

use DateTime;
use SeQura\Core\BusinessLogic\DataAccess\TransactionLog\Entities\TransactionLog;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\TransactionLog\RepositoryContracts\TransactionLogRepositoryInterface;
use SeQura\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use SeQura\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use SeQura\Core\Infrastructure\ORM\QueryFilter\Operators;
use SeQura\Core\Infrastructure\ORM\QueryFilter\QueryFilter;

/**
 * Class TransactionLogRepository
 *
 * @package SeQura\Core\BusinessLogic\DataAccess\TransactionLog\Repositories
 */
class TransactionLogRepository implements TransactionLogRepositoryInterface
{
    /**
     * @var RepositoryInterface Transaction log repository.
     */
    protected $repository;

    /**
     * @var StoreContext Store context needed for multistore environment.
     */
    protected $storeContext;

    /**
     * @param RepositoryInterface $repository
     * @param StoreContext $storeContext
     */
    public function __construct(RepositoryInterface $repository, StoreContext $storeContext)
    {
        $this->repository = $repository;
        $this->storeContext = $storeContext;
    }

    /**
     * @inheritDoc
     *
     * @throws QueryFilterInvalidParamException
     */
    public function getTransactionLog(string $merchantReference): ?TransactionLog
    {
        $entity = $this->getTransactionLogEntity($merchantReference);

        return $entity ?: null;
    }

    /**
     * @inheritDoc
     */
    public function setTransactionLog(TransactionLog $transactionLog): void
    {
        $this->repository->save($transactionLog);
    }

    /**
     * @param TransactionLog $transactionLog
     *
     * @return void
     */
    public function updateTransactionLog(TransactionLog $transactionLog): void
    {
        $this->repository->update($transactionLog);
    }

    /**
     * @param int $limit
     * @param int $offset
     * @param DateTime|null $disconnectTime
     *
     * @return TransactionLog[]
     *
     * @throws QueryFilterInvalidParamException
     */
    public function find(int $limit, int $offset, ?DateTime $disconnectTime = null): array
    {
        $queryFilter = new QueryFilter();
        $queryFilter->setLimit($limit);
        $queryFilter->setOffset($offset);
        $queryFilter->where('storeId', Operators::EQUALS, $this->storeContext->getStoreId())->orderBy(
            'id',
            QueryFilter::ORDER_DESC
        );

        if ($disconnectTime) {
            $queryFilter->where('timestamp', Operators::GREATER_THAN, $disconnectTime);
        }

        return $this->repository->select($queryFilter);
    }

    /**
     * @param int $executionId
     *
     * @return ?TransactionLog
     *
     * @throws QueryFilterInvalidParamException
     */
    public function getItemByExecutionId(int $executionId): ?TransactionLog
    {
        $queryFilter = new QueryFilter();
        $queryFilter
            ->where('executionId', Operators::EQUALS, $executionId)
            ->orderBy('id', QueryFilter::ORDER_DESC);

        /**
        * @var TransactionLog|null $transactionLog
        */
        $transactionLog = $this->repository->selectOne($queryFilter);

        return $transactionLog;
    }

    /**
     * @param DateTime|null $disconnectTime
     *
     * @return int
     *
     * @throws QueryFilterInvalidParamException
     */
    public function count(?DateTime $disconnectTime = null): int
    {
        $queryFilter = new QueryFilter();
        $queryFilter->where('storeId', Operators::EQUALS, $this->storeContext->getStoreId());

        if ($disconnectTime) {
            $queryFilter->where('timestamp', Operators::GREATER_THAN, $disconnectTime);
        }

        return $this->repository->count($queryFilter);
    }

    /**
     * @inheritDoc
     *
     * @throws QueryFilterInvalidParamException
     */
    public function findByMerchantReference(string $merchantReference): ?TransactionLog
    {
        $queryFilter = new QueryFilter();
        $queryFilter->where('merchantReference', Operators::EQUALS, $merchantReference);

        /**
        * @var TransactionLog|null $transactionLog
        */
        $transactionLog = $this->repository->selectOne($queryFilter);

        return $transactionLog;
    }

    /**
     * @inheritDoc
     *
     * @throws QueryFilterInvalidParamException
     */
    public function logsExist(DateTime $beforeDate): bool
    {
        $queryFilter = new QueryFilter();
        $queryFilter->where('timestamp', Operators::LESS_THAN, $beforeDate->getTimestamp())
            ->where('storeId', Operators::EQUALS, $this->storeContext->getStoreId());
        $result = $this->repository->count($queryFilter);

        return $result > 0;
    }

    /**
     * @inheritDoc
     *
     * @throws QueryFilterInvalidParamException
     */
    public function deleteLogs(DateTime $beforeDate, int $limit): void
    {
        $queryFilter = new QueryFilter();
        $queryFilter->where('timestamp', Operators::LESS_THAN, $beforeDate->getTimestamp())
            ->where('storeId', Operators::EQUALS, $this->storeContext->getStoreId());
        $queryFilter->setLimit($limit);

        $this->repository->deleteWhere($queryFilter);
    }

    /**
     * @inheritDoc
     *
     * @throws QueryFilterInvalidParamException
     */
    public function deleteTransactionLogById(int $id): void
    {
        $queryFilter = new QueryFilter();
        $queryFilter->where('id', Operators::EQUALS, $id);

        $this->repository->deleteWhere($queryFilter);
    }

    /**
     * @param string $merchantReference
     *
     * @return TransactionLog|null
     *
     * @throws QueryFilterInvalidParamException
     */
    protected function getTransactionLogEntity(string $merchantReference): ?TransactionLog
    {
        $queryFilter = new QueryFilter();
        $queryFilter->where('storeId', Operators::EQUALS, $this->storeContext->getStoreId())
            ->where('merchantReference', Operators::EQUALS, $merchantReference);

        /**
        * @var TransactionLog|null $transactionLog
        */
        $transactionLog = $this->repository->selectOne($queryFilter);

        return $transactionLog;
    }
}
