<?php

namespace SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Models;

use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Exceptions\InvalidExpressCheckoutPageException;

/**
 * Class ExpressCheckoutPageConfig
 *
 * Express Checkout configuration for a single storefront page.
 *
 * @package SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Models
 */
class ExpressCheckoutPageConfig
{
    /**
     * @var ExpressCheckoutPage
     */
    protected $page;

    /**
     * @var bool
     */
    protected $enabled;

    /**
     * @param ExpressCheckoutPage $page
     * @param bool $enabled Whether Express Checkout is enabled on that page.
     */
    public function __construct(ExpressCheckoutPage $page, bool $enabled)
    {
        $this->page = $page;
        $this->enabled = $enabled;
    }

    /**
     * @return ExpressCheckoutPage
     */
    public function getPage(): ExpressCheckoutPage
    {
        return $this->page;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'page' => $this->page->getPage(),
            'enabled' => $this->enabled,
        ];
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return self
     *
     * @throws InvalidExpressCheckoutPageException
     */
    public static function fromArray(array $data): self
    {
        return new self(
            ExpressCheckoutPage::parse((string)($data['page'] ?? '')),
            (bool)($data['enabled'] ?? false)
        );
    }
}
