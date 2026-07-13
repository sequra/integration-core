<?php

namespace SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Models;

use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Exceptions\InvalidExpressCheckoutPageException;

/**
 * Class ExpressCheckoutPage.
 *
 * Value object identifying a storefront page that may host an Express Checkout button.
 *
 * @package SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Models
 */
class ExpressCheckoutPage
{
    /**
     * Product page string constant.
     */
    private const PRODUCT = 'product';

    /**
     * Cart page string constant.
     */
    private const CART = 'cart';

    /**
     * Mini-cart page string constant.
     */
    private const MINI_CART = 'mini-cart';

    /**
     * @var string
     */
    private $page;

    /**
     * @param string $page
     */
    private function __construct(string $page)
    {
        $this->page = $page;
    }

    /**
     * Called for the product page.
     *
     * @return ExpressCheckoutPage
     */
    public static function product(): self
    {
        return new self(self::PRODUCT);
    }

    /**
     * Called for the cart page.
     *
     * @return ExpressCheckoutPage
     */
    public static function cart(): self
    {
        return new self(self::CART);
    }

    /**
     * Called for the mini-cart page.
     *
     * @return ExpressCheckoutPage
     */
    public static function miniCart(): self
    {
        return new self(self::MINI_CART);
    }

    /**
     * @return string
     */
    public function getPage(): string
    {
        return $this->page;
    }

    /**
     * Returns instance of ExpressCheckoutPage based on a page string.
     *
     * @param string $page
     *
     * @return self
     *
     * @throws InvalidExpressCheckoutPageException
     */
    public static function parse(string $page): self
    {
        if ($page === self::PRODUCT) {
            return self::product();
        }

        if ($page === self::CART) {
            return self::cart();
        }

        if ($page === self::MINI_CART) {
            return self::miniCart();
        }

        throw new InvalidExpressCheckoutPageException();
    }
}
