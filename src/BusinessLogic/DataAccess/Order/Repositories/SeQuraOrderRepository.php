<?php

namespace SeQura\Core\BusinessLogic\DataAccess\Order\Repositories;

use SeQura\Core\BusinessLogic\Domain\Order\Models\SeQuraOrder;
use SeQura\Core\BusinessLogic\Domain\Order\RepositoryContracts\SeQuraOrderRepositoryInterface;
use SeQura\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use SeQura\Core\Infrastructure\ORM\Interfaces\ConditionallyDeletes;
use SeQura\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use SeQura\Core\Infrastructure\ORM\QueryFilter\Operators;
use SeQura\Core\Infrastructure\ORM\QueryFilter\QueryFilter;

/**
 * Class SeQuraOrderRepository
 *
 * @package SeQura\Core\BusinessLogic\DataAccess\Order\Repositories
 */
class SeQuraOrderRepository implements SeQuraOrderRepositoryInterface
{
    /**
     * Operators matching an empty and a missing shop order reference.
     */
    private const UNLINKED_OPERATORS = [Operators::EQUALS, Operators::NULL];

    /**
     * @var RepositoryInterface SeQuraOrder repository.
     */
    protected $repository;

    /**
     * @param RepositoryInterface $repository
     */
    public function __construct(RepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getByShopReference(string $shopOrderReference): ?SeQuraOrder
    {
        $filter = new QueryFilter();
        $filter->where('orderRef1', Operators::EQUALS, $shopOrderReference)
            ->orderBy('id', QueryFilter::ORDER_DESC);

        /**
         * @var SeQuraOrder|null $result
         */
        $result = $this->repository->selectOne($filter);

        return $result;
    }

    public function getOrderBatchByShopReferences(array $shopOrderReferences): array
    {
        $filter = new QueryFilter();
        $filter->where('orderRef1', Operators::IN, $shopOrderReferences);

        /**
         * @var SeQuraOrder[] $result
         */
        $result = $this->repository->select($filter);

        return $result;
    }

    public function getByCartId(string $cartId): ?SeQuraOrder
    {
        $filter = new QueryFilter();
        $filter->where('cartId', Operators::EQUALS, $cartId)
            ->orderBy('id', QueryFilter::ORDER_DESC);

        /**
         * @var SeQuraOrder|null $result
         */
        $result = $this->repository->selectOne($filter);

        return $result;
    }

    public function getOrderBatchByCartIds(array $cartIds): array
    {
        $filter = new QueryFilter();
        $filter->where('cartId', Operators::IN, $cartIds);

        /**
         * @var SeQuraOrder[] $result
         */
        $result = $this->repository->select($filter);

        return $result;
    }

    public function getByOrderReference(string $sequraOrderReference): ?SeQuraOrder
    {
        /**
         * @var SeQuraOrder|null $result
         */
        $result = $this->repository->selectOne($this->referenceFilter($sequraOrderReference));

        return $result;
    }

    public function setSeQuraOrder(SeQuraOrder $order): void
    {
        /**
         * @var SeQuraOrder[] $savedOrders
         */
        $savedOrders = $this->repository->select($this->referenceFilter($order->getReference()));
        $savedOrder = array_shift($savedOrders);
        if (!$savedOrder) {
            $this->repository->save($order);

            return;
        }

        foreach ($savedOrders as $duplicatedOrder) {
            $this->repository->delete($duplicatedOrder);
        }

        $order->setId($savedOrder->getId());
        $this->repository->update($order);
    }

    public function deleteOrder(SeQuraOrder $existingOrder): void
    {
        $this->repository->delete($existingOrder);
    }

    /**
     * @return void
     */
    public function deleteAllOrders(): void
    {
        /**
 * @var SeQuraOrder[] $allEntities
*/
        $allEntities = $this->repository->select();

        foreach ($allEntities as $entity) {
            $this->repository->delete($entity);
        }
    }

    /**
     * @inheritDoc
     *
     * An order without a shop order reference stores it either empty or missing, so both are read and merged.
     *
     * @throws QueryFilterInvalidParamException
     */
    public function getUnlinkedOrdersAfter(int $afterId, int $limit): array
    {
        $orders = [];
        foreach (self::UNLINKED_OPERATORS as $operator) {
            $filter = $this->unlinkedFilter($operator)
                ->where('id', Operators::GREATER_THAN, $afterId)
                ->orderBy('id')
                ->setLimit($limit);

            /**
             * @var SeQuraOrder[] $batch
             */
            $batch = $this->repository->select($filter);
            $orders = array_merge($orders, $batch);
        }

        usort($orders, static function (SeQuraOrder $first, SeQuraOrder $second) {
            return (int)$first->getId() <=> (int)$second->getId();
        });

        return \array_slice($orders, 0, $limit);
    }

    /**
     * @inheritDoc
     */
    public function canDeleteUnlinkedOrders(): bool
    {
        return $this->repository instanceof ConditionallyDeletes;
    }

    /**
     * @inheritDoc
     *
     * The shop order reference is part of the delete condition, so an order linked since it was read is kept.
     * Nothing is deleted when the storage cannot delete by condition, since a delete by id would also remove an
     * order linked between reading and deleting it.
     *
     * @throws QueryFilterInvalidParamException
     */
    public function deleteUnlinkedOrders(array $ids): void
    {
        if (empty($ids) || !$this->repository instanceof ConditionallyDeletes) {
            return;
        }

        foreach (self::UNLINKED_OPERATORS as $operator) {
            $this->repository->deleteWhere($this->unlinkedFilter($operator)->where('id', Operators::IN, $ids));
        }
    }

    /**
     * @param string $operator Operators::EQUALS for an empty shop order reference, Operators::NULL for a missing one.
     *
     * @return QueryFilter
     *
     * @throws QueryFilterInvalidParamException
     */
    private function unlinkedFilter(string $operator): QueryFilter
    {
        return (new QueryFilter())->where('orderRef1', $operator, $operator === Operators::EQUALS ? '' : null);
    }

    /**
     * @param string $sequraOrderReference
     *
     * @return QueryFilter
     *
     * @throws QueryFilterInvalidParamException
     */
    private function referenceFilter(string $sequraOrderReference): QueryFilter
    {
        return (new QueryFilter())->where('reference', Operators::EQUALS, $sequraOrderReference);
    }
}
