<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\StoreIntegration\Models;

use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Exceptions\InvalidCapabilityException;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models\Capability;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;

/**
 * Class CapabilityModelTest.
 *
 * @package Domain\StoreIntegration\Models
 */
class CapabilityModelTest extends BaseTestCase
{
    /**
     * @return void
     *
     * @throws InvalidCapabilityException
     */
    public function testInvalidCapability(): void
    {
        // arrange
        $this->expectException(InvalidCapabilityException::class);
        // act

        Capability::parse('test');
        // assert
    }

    /**
     * @return void
     *
     * @throws InvalidCapabilityException
     */
    public function testGeneralCapability(): void
    {
        // arrange

        // act
        $capability = Capability::parse('general');

        // assert
        self::assertEquals('general', $capability->getCapability());
        self::assertEquals(Capability::general(), $capability);
    }

    /**
     * @return void
     *
     * @throws InvalidCapabilityException
     */
    public function testWidgetCapability(): void
    {
        // arrange

        // act
        $capability = Capability::parse('widget');

        // assert
        self::assertEquals('widget', $capability->getCapability());
        self::assertEquals(Capability::widget(), $capability);
    }

    /**
     * @return void
     *
     * @throws InvalidCapabilityException
     */
    public function testOrderStatusCapability(): void
    {
        // arrange

        // act
        $capability = Capability::parse('order-status');

        // assert
        self::assertEquals('order-status', $capability->getCapability());
        self::assertEquals(Capability::orderStatus(), $capability);
    }

    /**
     * @return void
     *
     * @throws InvalidCapabilityException
     */
    public function testStoreInfoCapability(): void
    {
        // arrange

        // act
        $capability = Capability::parse('store-info');

        // assert
        self::assertEquals('store-info', $capability->getCapability());
        self::assertEquals(Capability::storeInfo(), $capability);
    }

    /**
     * @return void
     *
     * @throws InvalidCapabilityException
     */
    public function testAdvancedCapability(): void
    {
        // arrange

        // act
        $capability = Capability::parse('advanced');

        // assert
        self::assertEquals('advanced', $capability->getCapability());
        self::assertEquals(Capability::advanced(), $capability);
    }
}
