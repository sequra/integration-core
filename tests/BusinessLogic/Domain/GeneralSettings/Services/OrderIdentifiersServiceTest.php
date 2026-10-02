<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\GeneralSettings\Services;

use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Services\OrderIdentifiersService;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockStoreInfoService;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockStoreInfoServiceWithOrderIdentifiers;

/**
 * Class OrderIdentifiersServiceTest.
 *
 * @package SeQura\Core\Tests\BusinessLogic\Domain\GeneralSettings\Services
 */
class OrderIdentifiersServiceTest extends BaseTestCase
{
    /**
     * @return void
     */
    public function testIntegrationPublishesNoOrderIdentifiers(): void
    {
        // Arrange
        $service = new OrderIdentifiersService(new MockStoreInfoService());

        // Act
        $identifiers = $service->getAvailableOrderIdentifiers();

        // Assert
        self::assertEquals([], $identifiers);
    }

    /**
     * @return void
     */
    public function testIntegrationPublishesItsOrderIdentifiers(): void
    {
        // Arrange
        $storeInfoService = new MockStoreInfoServiceWithOrderIdentifiers();
        $storeInfoService->setMockOrderIdentifiers([
            'orderId' => 'Order ID',
            'orderReference' => 'Order Reference',
        ]);
        $service = new OrderIdentifiersService($storeInfoService);

        // Act
        $identifiers = $service->getAvailableOrderIdentifiers();

        // Assert
        self::assertEquals([
            'orderId' => 'Order ID',
            'orderReference' => 'Order Reference',
        ], $identifiers);
    }
}
