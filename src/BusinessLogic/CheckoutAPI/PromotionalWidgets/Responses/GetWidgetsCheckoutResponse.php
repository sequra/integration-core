<?php

namespace SeQura\Core\BusinessLogic\CheckoutAPI\PromotionalWidgets\Responses;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\Widget;

/**
 * Class GetWidgetsCheckoutResponse.
 *
 * @package SeQura\Core\BusinessLogic\CheckoutAPI\PromotionalWidgets\Responses
 */
class GetWidgetsCheckoutResponse extends Response
{
    /**
     * @var Widget[]
     */
    protected $widgets;

    /**
     * @param Widget[] $widgets
     */
    public function __construct(
        array $widgets
    ) {
        $this->widgets = $widgets;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $arrayOfWidgets = [];
        foreach ($this->widgets as $widget) {
            $arrayOfWidgets[] = [
                'product' => $widget->getProduct(),
                'dest' => $widget->getDest(),
                'theme' => $widget->getTheme(),
                'reverse' => $widget->getReverse(),
                'campaign' => $widget->getCampaign(),
                'priceSel' => $widget->getPriceSelector(),
                'altPriceSel' => $widget->getAltPriceSelector(),
                'altTriggerSelector' => $widget->getAltTriggerSelector(),
                'minAmount' => $widget->getMinAmount(),
                'maxAmount' => $widget->getMaxAmount()
            ];
        }

        return $arrayOfWidgets;
    }
}
