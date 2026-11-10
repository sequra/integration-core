<?php

namespace SeQura\Core\BusinessLogic\Domain\Order\RepositoryContracts;

use SeQura\Core\BusinessLogic\Domain\Order\Models\SeQuraOrder;

/**
 * Interface SeQuraOrderRepository
 *
 * @package SeQura\Core\BusinessLogic\Domain\Order\RepositoryContracts
 */
interface SeQuraOrderRepositoryInterface
{
    /**
     * Gets the latest Sequra order by shop order reference
     *
     * @param string $shopOrderReference
     *
     * @return SeQuraOrder|null
     */
    public function getByShopReference(string $shopOrderReference): ?SeQuraOrder;

    /**
     * Gets Sequra orders by shop order references
     *
     * @param string[] $shopOrderReferences
     *
     * @return SeQuraOrder[]
     */
    public function getOrderBatchByShopReferences(array $shopOrderReferences): array;

    /**
     * Gets the latest Sequra order by shop cart/quote reference. A cart keeps the orders it was solicited for before,
     * as SeQura may still approve them.
     *
     * @param string $cartId
     *
     * @return SeQuraOrder|null
     */
    public function getByCartId(string $cartId): ?SeQuraOrder;

    /**
     * Gets Sequra orders by shop cart/quote references
     *
     * @param string[] $cartIds
     *
     * @return SeQuraOrder[]
     */
    public function getOrderBatchByCartIds(array $cartIds): array;

    /**
     * Gets Sequra order by Sequra order reference
     *
     * @param string $sequraOrderReference
     *
     * @return SeQuraOrder|null
     */
    public function getByOrderReference(string $sequraOrderReference): ?SeQuraOrder;

    /**
     * Insert/update SeQuraOrder for current store context.
     *
     * @param SeQuraOrder $order
     *
     * @return void
     */
    public function setSeQuraOrder(SeQuraOrder $order): void;

    /**
     * Deletes Sequra order form storage
     *
     * @param SeQuraOrder $existingOrder
     *
     * @return void
     */
    public function deleteOrder(SeQuraOrder $existingOrder): void;

    /**
     * Deletes all Sequra order form storage
     *
     * @return void
     */
    public function deleteAllOrders(): void;

    /**
     * Gets the next orders without a shop order reference whose id is greater than the given one, in id order.
     *
     * @param int $afterId
     * @param int $limit
     *
     * @return SeQuraOrder[]
     */
    public function getUnlinkedOrdersAfter(int $afterId, int $limit): array;

    /**
     * Deletes the orders with the given ids that still have no shop order reference, or none at all when the storage
     * cannot check that at the moment it deletes.
     *
     * @param int[] $ids
     *
     * @return void
     */
    public function deleteUnlinkedOrders(array $ids): void;
}
