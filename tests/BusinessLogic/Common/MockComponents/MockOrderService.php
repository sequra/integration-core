<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use SeQura\Core\BusinessLogic\Domain\Integration\Order\OrderServiceInterface;

/**
 * Class MockOrderService
 *
 * @package SeQura\Core\Tests\BusinessLogic\Common\MockComponents
 */
class MockOrderService implements OrderServiceInterface
{
    /**
     * @inheritDoc
     */
    public function getOrderUrl(string $merchantReference): string
    {
        return 'https.test.url/' . $merchantReference;
    }
}
