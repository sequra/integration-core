<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\StoreIntegration\Models;

use PHPUnit\Framework\TestCase;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Exceptions\InvalidCapabilityException;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models\Capability;

/**
 * Class CapabilityTest.
 *
 * @package SeQura\Core\Tests\BusinessLogic\Domain\StoreIntegration\Models
 */
class CapabilityTest extends TestCase
{
    /**
     * @return array<string, array{0: callable(): Capability, 1: string}>
     */
    public function factoryProvider(): array
    {
        return [
            'general' => [static function (): Capability {
                return Capability::general();
            }, 'general'],
            'widget' => [static function (): Capability {
                return Capability::widget();
            }, 'widget'],
            'orderStatus' => [static function (): Capability {
                return Capability::orderStatus();
            }, 'order-status'],
            'storeInfo' => [static function (): Capability {
                return Capability::storeInfo();
            }, 'store-info'],
            'advanced' => [static function (): Capability {
                return Capability::advanced();
            }, 'advanced'],
            'hostedCheckout' => [static function (): Capability {
                return Capability::hostedCheckout();
            }, 'hosted-checkout'],
            'listingSelectors' => [static function (): Capability {
                return Capability::listingSelectors();
            }, 'listing-selectors'],
            'altProductPrice' => [static function (): Capability {
                return Capability::altProductPrice();
            }, 'alt-product-price'],
            'expressCheckout' => [static function (): Capability {
                return Capability::expressCheckout();
            }, 'express-checkout'],
        ];
    }

    /**
     * @dataProvider factoryProvider
     *
     * @param callable():Capability $factory
     * @param string $expected
     *
     * @return void
     */
    public function testFactoryReturnsExpectedValue(callable $factory, string $expected): void
    {
        self::assertSame($expected, $factory()->getCapability());
    }

    /**
     * @dataProvider factoryProvider
     *
     * @param callable():Capability $factory
     * @param string $expected
     *
     * @return void
     *
     * @throws InvalidCapabilityException
     */
    public function testParseRoundtripsThroughFactory(callable $factory, string $expected): void
    {
        self::assertSame($expected, Capability::parse($expected)->getCapability());
    }

    /**
     * @return void
     */
    public function testParseThrowsForUnknownCapability(): void
    {
        $this->expectException(InvalidCapabilityException::class);

        Capability::parse('not-a-real-capability');
    }
}
