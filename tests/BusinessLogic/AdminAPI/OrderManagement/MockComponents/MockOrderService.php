<?php

namespace SeQura\Core\Tests\BusinessLogic\AdminAPI\OrderManagement\MockComponents;

use Exception;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderUpdateData;
use SeQura\Core\BusinessLogic\Domain\Order\Models\SeQuraOrder;
use SeQura\Core\BusinessLogic\Domain\Order\Service\OrderService;
use Throwable;

/**
 * Class MockOrderService
 *
 * @package SeQura\Core\Tests\BusinessLogic\AdminAPI\OrderManagement\MockComponents
 */
class MockOrderService extends OrderService
{
    /**
     * @var OrderUpdateData|null
     */
    private $lastUpdateData;

    /**
     * @var string|null
     */
    private $storeIdOfLastUpdate;

    /**
     * @var SeQuraOrder|null
     */
    private $updatedOrder;

    /**
     * @var Throwable|null
     */
    private $updateException;

    /**
     * @var string[]|null
     */
    private $lastMerchantReferenceUpdate;

    /**
     * @param SeQuraOrder $order
     *
     * @return void
     */
    public function setUpdatedOrder(SeQuraOrder $order): void
    {
        $this->updatedOrder = $order;
    }

    /**
     * @param Throwable $exception
     *
     * @return void
     */
    public function setUpdateException(Throwable $exception): void
    {
        $this->updateException = $exception;
    }

    /**
     * @return string[]|null The cart id, the shop reference and the store id of the last change.
     */
    public function getLastMerchantReferenceUpdate(): ?array
    {
        return $this->lastMerchantReferenceUpdate;
    }

    /**
     * @return OrderUpdateData|null
     */
    public function getLastUpdateData(): ?OrderUpdateData
    {
        return $this->lastUpdateData;
    }

    /**
     * @return string|null
     */
    public function getStoreIdOfLastUpdate(): ?string
    {
        return $this->storeIdOfLastUpdate;
    }

    /**
     * @inheritDoc
     *
     * @throws Exception|Throwable
     */
    public function updateOrder(OrderUpdateData $orderUpdateData): SeQuraOrder
    {
        $this->lastUpdateData = $orderUpdateData;
        $this->storeIdOfLastUpdate = StoreContext::getInstance()->getStoreId();

        if ($this->updateException) {
            throw $this->updateException;
        }

        if (!$this->updatedOrder) {
            throw new Exception('No order set on the mock.');
        }

        return $this->updatedOrder;
    }

    /**
     * @inheritDoc
     *
     * @throws Throwable
     */
    public function updateMerchantReference(string $cartId, string $shopReference): void
    {
        $this->lastMerchantReferenceUpdate = [$cartId, $shopReference, StoreContext::getInstance()->getStoreId()];

        if ($this->updateException) {
            throw $this->updateException;
        }
    }
}
