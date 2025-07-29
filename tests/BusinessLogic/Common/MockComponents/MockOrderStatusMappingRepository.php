<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\Models\OrderStatusMapping;
use SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\RepositoryContracts\OrderStatusSettingsRepositoryInterface;

/**
 * Class MockOrderStatusMappingRepository.
 *
 * @package SeQura\Core\Tests\BusinessLogic\Common\MockComponents
 */
class MockOrderStatusMappingRepository implements OrderStatusSettingsRepositoryInterface
{
    /**
     * @var OrderStatusMapping[]
     */
    private $orderStatusMapping = [];

    /**
     * @inheritDoc
     */
    public function getOrderStatusMapping(): ?array
    {
        return $this->orderStatusMapping;
    }

    /**
     * @inheritDoc
     */
    public function setOrderStatusMapping(array $orderStatusMapping): void
    {
        $this->orderStatusMapping = $orderStatusMapping;
    }

    /**
     * @inheritDoc
     */
    public function deleteOrderStatusMapping(): void
    {
        $this->orderStatusMapping = [];
    }
}
