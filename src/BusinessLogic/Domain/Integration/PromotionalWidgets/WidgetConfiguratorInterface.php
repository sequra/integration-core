<?php

namespace SeQura\Core\BusinessLogic\Domain\Integration\PromotionalWidgets;

use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\WidgetSettings;

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

    /**
     * Returns an instance of WidgetSettings having the default values.
     * See SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\WidgetSettings::createDefault().
     */
    public function getDefaultWidgetSettings(): WidgetSettings;
}
