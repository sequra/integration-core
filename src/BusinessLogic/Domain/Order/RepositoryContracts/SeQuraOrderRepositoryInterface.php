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
     * Gets Sequra order by shop order reference
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
     * Gets Sequra order by shop cart/quote reference
     *
     * @param string $cartId
     *
     * @return SeQuraOrder|null
     */
    public function getByCartId(string $cartId): ?SeQuraOrder;

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
}
