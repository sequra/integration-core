<?php

namespace SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models;

use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Exceptions\InvalidCapabilityException;

/**
 * Class Capability.
 *
 * @package SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models
 */
class Capability
{
    /**
     * General capability string constant.
     */
    private const GENERAL = 'general';

    /**
     * Widget capability string constant.
     */
    private const WIDGET = 'widget';

    /**
     * Order status capability string constant.
     */
    private const ORDER_STATUS = 'order-status';

    /**
     * Store info capability string constant.
     */
    private const STORE_INFO = 'store-info';

    /**
     * Advanced capability string constant.
     */
    private const ADVANCED = 'advanced';

    /**
     * Hosted checkout capability string constant.
     */
    private const HOSTED_CHECKOUT = 'hosted-checkout';

    /**
     * Listing selectors capability string constant.
     */
    private const LISTING_SELECTORS = 'listing-selectors';

    /**
     * Alt product price capability string constant.
     */
    private const ALT_PRODUCT_PRICE = 'alt-product-price';

    /**
     * @var string
     */
    private $capability;

    /**
     * @param string $capability
     */
    private function __construct(string $capability)
    {
        $this->capability = $capability;
    }

    /**
     * Called for general capability.
     *
     * @return Capability
     */
    public static function general(): self
    {
        return new self(self::GENERAL);
    }

    /**
     * Called for widget capability.
     *
     * @return Capability
     */
    public static function widget(): self
    {
        return new self(self::WIDGET);
    }

    /**
     * Called for order status capability.
     *
     * @return Capability
     */
    public static function orderStatus(): self
    {
        return new self(self::ORDER_STATUS);
    }

    /**
     * Called for store info capability.
     *
     * @return Capability
     */
    public static function storeInfo(): self
    {
        return new self(self::STORE_INFO);
    }

    /**
     * Called for advanced capability.
     *
     * @return Capability
     */
    public static function advanced(): self
    {
        return new self(self::ADVANCED);
    }

    /**
     * Called for Hosted checkout capability.
     *
     * @return Capability
     */
    public static function hostedCheckout(): self
    {
        return new self(self::HOSTED_CHECKOUT);
    }

    /**
     * Called for Listing selectors capability.
     *
     * @return Capability
     */
    public static function listingSelectors(): self
    {
        return new self(self::LISTING_SELECTORS);
    }

    /**
     * Called for Alt product price capability.
     *
     * @return Capability
     */
    public static function altProductPrice(): self
    {
        return new self(self::ALT_PRODUCT_PRICE);
    }

    /**
     * @return string
     */
    public function getCapability(): string
    {
        return $this->capability;
    }

    /**
     * Returns instance of Capability based on mode string.
     *
     * @param string $capability
     *
     * @return self
     *
     * @throws InvalidCapabilityException
     */
    public static function parse(string $capability): self
    {
        if ($capability === self::GENERAL) {
            return self::general();
        }

        if ($capability === self::WIDGET) {
            return self::widget();
        }

        if ($capability === self::ORDER_STATUS) {
            return self::orderStatus();
        }

        if ($capability === self::STORE_INFO) {
            return self::storeInfo();
        }

        if ($capability === self::ADVANCED) {
            return self::advanced();
        }

        if ($capability === self::HOSTED_CHECKOUT) {
            return self::hostedCheckout();
        }

        if ($capability === self::LISTING_SELECTORS) {
            return self::listingSelectors();
        }

        if ($capability === self::ALT_PRODUCT_PRICE) {
            return self::altProductPrice();
        }

        throw new InvalidCapabilityException();
    }
}
