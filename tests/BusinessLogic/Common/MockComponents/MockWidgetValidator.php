<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Services\WidgetValidationService;

/**
 * Class MockWidgetValidator.
 *
 * @package SeQura\Core\Tests\BusinessLogic\Common\MockComponents
 */
class MockWidgetValidator extends WidgetValidationService
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
     * Validates if current IP address on checkout, if set in general settings, is supported.
     *
     * @param string $currentIpAddress
     *
     * @return bool
     */
    public function isIpAddressValid(string $currentIpAddress): bool
    {
        return $this->ipAddressValid;
    }

    /**
     * Returns true if current currency on checkout is supported for widgets.
     *
     * @param string $currentCurrency
     *
     * @return bool
     */
    public function isCurrencySupported(string $currentCurrency): bool
    {
        return $this->currencyValid;
    }

    /**
     * Returns true if products sku and category are not excluded in SeQura administration.
     *
     * @param string $productId
     *
     * @return bool
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
