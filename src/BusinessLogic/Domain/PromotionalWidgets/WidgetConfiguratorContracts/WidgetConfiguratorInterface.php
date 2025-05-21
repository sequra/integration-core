<?php

namespace SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\WidgetConfiguratorContracts;

/**
 * Interface WidgetConfiguratorInterface
 *
 * @package SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\WidgetConfiguratorContracts
 */
interface WidgetConfiguratorInterface
{
    /**
     * Returns locale
     *
     * @return ?string
     */
    public function getLocale(): ?string;

    /**
     * Returns currency
     *
     * @return ?string
     */
    public function getCurrency(): ?string;

    /**
     * Returns decimal separator
     *
     * @return ?string
     */
    public function getDecimalSeparator(): ?string;

    /**
     * Returns thousand separator
     *
     * @return ?string
     */
    public function getThousandsSeparator(): ?string;
}
