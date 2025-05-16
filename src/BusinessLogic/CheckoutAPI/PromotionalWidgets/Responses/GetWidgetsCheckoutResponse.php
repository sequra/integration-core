<?php

namespace SeQura\Core\BusinessLogic\CheckoutAPI\PromotionalWidgets\Responses;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\Widget;

/**
 * Class PromotionalWidgetsCheckoutResponse.
 *
 * @package SeQura\Core\BusinessLogic\CheckoutAPI\PromotionalWidgets\Responses
 */
class GetWidgetsCheckoutResponse extends Response
{
    /**
     * @var Widget
     */
    protected $widget;

    /**
     * @param Widget $widget
     */
    public function __construct(
        Widget $widget
    ) {
        $this->widget = $widget;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            'product' => $this->widget->getProduct(),
            'dest' => $this->widget->getDest(),
            'theme' => $this->widget->getTheme(),
            'reverse' => $this->widget->getReverse(),
            'campaign' => $this->widget->getCampaign(),
            'priceSel' => $this->widget->getPriceSel(),
            'altPriceSel' => $this->widget->getAltPriceSel(),
            'isAltSel' => $this->widget->isAltSel(),
        ];
    }
}
