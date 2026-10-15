<?php

namespace SeQura\Core\BusinessLogic\DataAccess\Order\Repositories;

use SeQura\Core\BusinessLogic\Domain\Order\Models\SeQuraOrder;
use SeQura\Core\BusinessLogic\Domain\Order\RepositoryContracts\SeQuraOrderRepositoryInterface;
use SeQura\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
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
