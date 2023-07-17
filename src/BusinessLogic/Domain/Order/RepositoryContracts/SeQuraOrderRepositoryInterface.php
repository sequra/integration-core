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
     * @return SeQuraOrder|null
     */
    public function getByShopReference(string $shopOrderReference): ?SeQuraOrder;

    /**
     * Gets Sequra order by shop cart/quote reference
     *
     * @param string $cartId
     * @return SeQuraOrder|null
     */
    public function getByCartId(string $cartId): ?SeQuraOrder;

    /**
     * Gets Sequra order by Sequra order reference
     *
     * @param string $sequraOrderReference
     * @return SeQuraOrder|null
     */
    public function getByOrderReference(string $sequraOrderReference): ?SeQuraOrder;

    /**
     * Insert/update SeQuraOrder for current store context.
     *
     * @param SeQuraOrder $order
     * @return void
     */
    public function setSeQuraOrder(SeQuraOrder $order): void;
}
