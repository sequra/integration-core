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
    private $valid = true;

    /**
     * @param string $currentCurrency
     * @param string $currentIpAddress
     *
     * @return bool
     */
    public function validateCurrentCurrencyAndIpAddress(string $currentCurrency, string $currentIpAddress): bool
    {
        return $this->valid;
    }

    /**
     * @param bool $valid
     *
     * @return void
     */
    public function setValid(bool $valid): void
    {
        $this->valid = $valid;
    }
}
