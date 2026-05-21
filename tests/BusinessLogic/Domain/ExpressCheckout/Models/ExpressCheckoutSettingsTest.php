<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\ExpressCheckout\Models;

use PHPUnit\Framework\TestCase;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Exceptions\DuplicatedExpressCheckoutPageException;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Exceptions\InvalidExpressCheckoutPageConfigException;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Models\ExpressCheckoutPage;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Models\ExpressCheckoutPageConfig;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Models\ExpressCheckoutSettings;
use stdClass;

/**
 * Class ExpressCheckoutSettingsTest.
 *
 * @package SeQura\Core\Tests\BusinessLogic\Domain\ExpressCheckout\Models
 */
class ExpressCheckoutSettingsTest extends TestCase
{
    /**
     * @return void
     */
    public function testConstructorAcceptsEmptyArrayByDefault(): void
    {
        $settings = new ExpressCheckoutSettings();

        self::assertSame([], $settings->getExpressCheckoutConfigs());
    }

    /**
     * @return void
     *
     * @throws DuplicatedExpressCheckoutPageException
     * @throws InvalidExpressCheckoutPageConfigException
     */
    public function testConstructorStoresValidConfigsInOrder(): void
    {
        $product = new ExpressCheckoutPageConfig(ExpressCheckoutPage::product(), true);
        $cart = new ExpressCheckoutPageConfig(ExpressCheckoutPage::cart(), false);
        $miniCart = new ExpressCheckoutPageConfig(ExpressCheckoutPage::miniCart(), true);

        $settings = new ExpressCheckoutSettings([$product, $cart, $miniCart]);

        self::assertSame([$product, $cart, $miniCart], $settings->getExpressCheckoutConfigs());
    }

    /**
     * @return void
     *
     * @throws DuplicatedExpressCheckoutPageException
     * @throws InvalidExpressCheckoutPageConfigException
     */
    public function testConstructorReindexesAssociativeKeys(): void
    {
        $product = new ExpressCheckoutPageConfig(ExpressCheckoutPage::product(), true);
        $cart = new ExpressCheckoutPageConfig(ExpressCheckoutPage::cart(), false);

        $settings = new ExpressCheckoutSettings(['a' => $product, 'b' => $cart]);

        self::assertSame([0, 1], array_keys($settings->getExpressCheckoutConfigs()));
    }

    /**
     * @return void
     *
     * @throws DuplicatedExpressCheckoutPageException
     */
    public function testConstructorThrowsWhenAnEntryIsNotAPageConfig(): void
    {
        $this->expectException(InvalidExpressCheckoutPageConfigException::class);

        new ExpressCheckoutSettings([
            new ExpressCheckoutPageConfig(ExpressCheckoutPage::product(), true),
            new stdClass(),
        ]);
    }

    /**
     * @return void
     *
     * @throws InvalidExpressCheckoutPageConfigException
     */
    public function testConstructorThrowsOnDuplicatePages(): void
    {
        $this->expectException(DuplicatedExpressCheckoutPageException::class);

        new ExpressCheckoutSettings([
            new ExpressCheckoutPageConfig(ExpressCheckoutPage::product(), true),
            new ExpressCheckoutPageConfig(ExpressCheckoutPage::cart(), false),
            new ExpressCheckoutPageConfig(ExpressCheckoutPage::product(), false),
        ]);
    }

    /**
     * @return void
     *
     * @throws DuplicatedExpressCheckoutPageException
     * @throws InvalidExpressCheckoutPageConfigException
     */
    public function testIsPageEnabledReturnsTrueForEnabledPage(): void
    {
        $settings = new ExpressCheckoutSettings([
            new ExpressCheckoutPageConfig(ExpressCheckoutPage::product(), true),
            new ExpressCheckoutPageConfig(ExpressCheckoutPage::cart(), false),
        ]);

        self::assertTrue($settings->isPageEnabled(ExpressCheckoutPage::product()->getPage()));
    }

    /**
     * @return void
     *
     * @throws DuplicatedExpressCheckoutPageException
     * @throws InvalidExpressCheckoutPageConfigException
     */
    public function testIsPageEnabledReturnsFalseForDisabledPage(): void
    {
        $settings = new ExpressCheckoutSettings([
            new ExpressCheckoutPageConfig(ExpressCheckoutPage::product(), true),
            new ExpressCheckoutPageConfig(ExpressCheckoutPage::cart(), false),
        ]);

        self::assertFalse($settings->isPageEnabled(ExpressCheckoutPage::cart()->getPage()));
    }

    /**
     * @return void
     *
     * @throws DuplicatedExpressCheckoutPageException
     * @throws InvalidExpressCheckoutPageConfigException
     */
    public function testIsPageEnabledReturnsFalseForUnknownPage(): void
    {
        $settings = new ExpressCheckoutSettings([
            new ExpressCheckoutPageConfig(ExpressCheckoutPage::product(), true),
        ]);

        self::assertFalse($settings->isPageEnabled(ExpressCheckoutPage::miniCart()->getPage()));
    }

    /**
     * @return void
     */
    public function testIsPageEnabledReturnsFalseWhenNoConfigsStored(): void
    {
        $settings = new ExpressCheckoutSettings();

        self::assertFalse($settings->isPageEnabled(ExpressCheckoutPage::product()->getPage()));
    }

    /**
     * @return void
     *
     * @throws DuplicatedExpressCheckoutPageException
     * @throws InvalidExpressCheckoutPageConfigException
     */
    public function testToArraySerializesConfigs(): void
    {
        $settings = new ExpressCheckoutSettings([
            new ExpressCheckoutPageConfig(ExpressCheckoutPage::product(), true),
            new ExpressCheckoutPageConfig(ExpressCheckoutPage::cart(), false),
            new ExpressCheckoutPageConfig(ExpressCheckoutPage::miniCart(), true),
        ]);

        self::assertSame([
            'expressCheckoutConfigs' => [
                ['page' => 'product', 'enabled' => true],
                ['page' => 'cart', 'enabled' => false],
                ['page' => 'mini-cart', 'enabled' => true],
            ],
        ], $settings->toArray());
    }

    /**
     * @return void
     */
    public function testToArrayReturnsEmptyConfigsForDefaultConstruction(): void
    {
        $settings = new ExpressCheckoutSettings();

        self::assertSame(['expressCheckoutConfigs' => []], $settings->toArray());
    }
}
