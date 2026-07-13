<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\ExpressCheckout\Models;

use PHPUnit\Framework\TestCase;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Exceptions\InvalidExpressCheckoutPageException;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Models\ExpressCheckoutPage;

/**
 * Class ExpressCheckoutPageTest.
 *
 * @package SeQura\Core\Tests\BusinessLogic\Domain\ExpressCheckout\Models
 */
class ExpressCheckoutPageTest extends TestCase
{
    /**
     * @return void
     */
    public function testProductFactoryReturnsProductPage(): void
    {
        self::assertSame('product', ExpressCheckoutPage::product()->getPage());
    }

    /**
     * @return void
     */
    public function testCartFactoryReturnsCartPage(): void
    {
        self::assertSame('cart', ExpressCheckoutPage::cart()->getPage());
    }

    /**
     * @return void
     */
    public function testMiniCartFactoryReturnsMiniCartPage(): void
    {
        self::assertSame('mini-cart', ExpressCheckoutPage::miniCart()->getPage());
    }

    /**
     * @return void
     *
     * @throws InvalidExpressCheckoutPageException
     */
    public function testParseProduct(): void
    {
        self::assertSame('product', ExpressCheckoutPage::parse('product')->getPage());
    }

    /**
     * @return void
     *
     * @throws InvalidExpressCheckoutPageException
     */
    public function testParseCart(): void
    {
        self::assertSame('cart', ExpressCheckoutPage::parse('cart')->getPage());
    }

    /**
     * @return void
     *
     * @throws InvalidExpressCheckoutPageException
     */
    public function testParseMiniCart(): void
    {
        self::assertSame('mini-cart', ExpressCheckoutPage::parse('mini-cart')->getPage());
    }

    /**
     * @return void
     */
    public function testParseThrowsOnUnknownPage(): void
    {
        $this->expectException(InvalidExpressCheckoutPageException::class);

        ExpressCheckoutPage::parse('checkout');
    }

    /**
     * @return void
     */
    public function testParseThrowsOnEmptyString(): void
    {
        $this->expectException(InvalidExpressCheckoutPageException::class);

        ExpressCheckoutPage::parse('');
    }

    /**
     * @return void
     */
    public function testParseIsCaseSensitive(): void
    {
        $this->expectException(InvalidExpressCheckoutPageException::class);

        ExpressCheckoutPage::parse('Product');
    }
}
