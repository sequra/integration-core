<?php

namespace SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\WidgetConfiguratorContracts;

/**
 * Interface MiniWidgetMessagesProviderInterface
 *
 * @package SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\WidgetConfiguratorContracts
 */
interface MiniWidgetMessagesProviderInterface
{
    public const MINI_WIDGET_MESSAGE = [
        "ES" => "Desde %s/mes",
        "FR" => "À partir de %s/mois",
        "IT" => "Da %s/mese",
        "PT" => "De %s/mês"
    ];

    public const MINI_WIDGET_BELOW_LIMIT_MESSAGE = [
        "ES" => "Fracciona a partir de %s",
        "FR" => "Fraction de %s",
        "IT" => "Frazione da %s",
        "PT" => "Fração de %s"
    ];

    /**
     * Returns mini widget message
     *
     * @return ?string
     */
    public function getMessage(): ?string;

    /**
     * Returns mini widget below limit message
     *
     * @return ?string
     */
    public function getBelowLimitMessage(): ?string;
}
