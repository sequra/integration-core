<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use SeQura\Core\BusinessLogic\Domain\Integration\ShopOrderStatuses\ShopOrderStatusesServiceInterface;
use SeQura\Core\BusinessLogic\Domain\OrderStatus\Models\OrderStatus;

/**
 * Class MockShopOrderStatusesService
 *
 * @package SeQura\Core\Tests\BusinessLogic\Common\MockComponents
 */
class MockShopOrderStatusesService implements ShopOrderStatusesServiceInterface
{
    /**
     * @inheritDoc
     */
    public function getShopOrderStatuses(): array
    {
        return [
            new OrderStatus('1', 'Success'),
            new OrderStatus('2', 'Failed'),
            new OrderStatus('3', 'Hold'),
        ];
    }
}
