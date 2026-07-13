<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use SeQura\Core\BusinessLogic\Domain\Checkout\Services\CheckoutService;

/**
 * Class MockCheckoutValidator.
 *
 * @package SeQura\Core\Tests\BusinessLogic\Common\MockComponents
 */
class MockCheckoutValidator extends CheckoutService
{
    /**
     * @var bool
     */
    private $ipAddressValid = true;

    /**
     * @var bool
     */
    private $currencyValid = true;

    /**
     * @var bool
     */
    private $productValid = true;

    /**
     * @inheritDoc
     */
    public function isIpAddressValid(string $currentIpAddress): bool
    {
        return $this->ipAddressValid;
    }

    /**
     * @inheritDoc
     */
    public function isCurrencySupported(string $currentCurrency): bool
    {
        return $this->currencyValid;
    }

    /**
     * @inheritDoc
     */
    public function isProductSupported(string $productId): bool
    {
        return $this->productValid;
    }

    /**
     * @param bool $valid
     *
     * @return void
     */
    public function setAddressValid(bool $valid): void
    {
        $this->ipAddressValid = $valid;
    }

    /**
     * @param bool $valid
     *
     * @return void
     */
    public function setCurrencyValid(bool $valid): void
    {
        $this->currencyValid = $valid;
    }

    /**
     * @param bool $valid
     *
     * @return void
     */
    public function setProductValid(bool $valid): void
    {
        $this->productValid = $valid;
    }
}
