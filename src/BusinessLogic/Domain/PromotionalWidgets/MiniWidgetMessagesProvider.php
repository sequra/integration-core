<?php

namespace SeQura\Core\BusinessLogic\Domain\PromotionalWidgets;

/**
 * Interface MiniWidgetMessagesProvider
 *
 * @package SeQura\Core\BusinessLogic\Domain\PromotionalWidget
 */
class MiniWidgetMessagesProvider
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
}
