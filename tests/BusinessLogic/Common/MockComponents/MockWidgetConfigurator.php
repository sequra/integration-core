<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\WidgetConfiguratorContracts\WidgetConfiguratorInterface;

/**
 * Class MockWidgetConfigurator.
 *
 * @package SeQura\Core\Tests\BusinessLogic\Common\MockComponents
 */
class MockWidgetConfigurator implements WidgetConfiguratorInterface
{
    /**
     * @var ?string
     */
    private $locale = null;
    /**
     * @var ?string
     */
    private $currency = null;
    /**
     * @var ?string
     */
    private $decimalSeparator = null;
    /**
     * @var ?string
     */
    private $thousandsSeparator = null;

    /**
     * @inheritDoc
     */
    public function getLocale(): ?string
    {
        return $this->locale;
    }

    /**
     * @inheritDoc
     */
    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    /**
     * @inheritDoc
     */
    public function getDecimalSeparator(): ?string
    {
        return $this->decimalSeparator;
    }

    /**
     * @inheritDoc
     */
    public function getThousandsSeparator(): ?string
    {
        return $this->thousandsSeparator;
    }

    /**
     * @param string $locale
     *
     * @return void
     */
    public function setMockLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    /**
     * @param string $currency
     *
     * @return void
     */
    public function setMockCurrency(string $currency): void
    {
        $this->currency = $currency;
    }

    /**
     * @param string $decimalSelector
     *
     * @return void
     */
    public function setMockDecimalSeparator(string $decimalSelector): void
    {
        $this->decimalSeparator = $decimalSelector;
    }

    /**
     * @param string $thousandsSeparator
     *
     * @return void
     */
    public function setMockThousandSeparator(string $thousandsSeparator): void
    {
        $this->thousandsSeparator = $thousandsSeparator;
    }
}
