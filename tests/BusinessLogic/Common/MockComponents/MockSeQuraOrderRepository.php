<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use SeQura\Core\BusinessLogic\Domain\Order\Models\SeQuraOrder;
use SeQura\Core\BusinessLogic\Domain\Order\RepositoryContracts\SeQuraOrderRepositoryInterface;

/**
 * Class MockSeQuraOrderRepository.
 *
 * @package SeQura\Core\Tests\BusinessLogic\Common\MockComponents
 */
class MockSeQuraOrderRepository implements SeQuraOrderRepositoryInterface
{
    /**
     * @var SeQuraOrder[]
     */
    private $orders = [];

    /**
     * @inheritDoc
     */
    public function getByShopReference(string $shopOrderReference): ?SeQuraOrder
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getOrderBatchByShopReferences(array $shopOrderReferences): array
    {
        return $this->orders;
    }

    /**
     * @inheritDoc
     */
    public function getByCartId(string $cartId): ?SeQuraOrder
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getByOrderReference(string $sequraOrderReference): ?SeQuraOrder
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function setSeQuraOrder(SeQuraOrder $order): void
    {
        $this->orders[] = $order;
    }

    /**
     * @inheritDoc
     */
    public function deleteOrder(SeQuraOrder $existingOrder): void
    {
    }

    /**
     * @inheritDoc
     */
    public function deleteAllOrders(): void
    {
        $this->orders = [];
    }
}
