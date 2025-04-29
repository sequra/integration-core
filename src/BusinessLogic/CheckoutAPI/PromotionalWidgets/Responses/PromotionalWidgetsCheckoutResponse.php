<?php

namespace SeQura\Core\BusinessLogic\CheckoutAPI\PromotionalWidgets\Responses;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\WidgetInitializer;

/**
 * Class PromotionalWidgetsCheckoutResponse.
 *
 * @package SeQura\Core\BusinessLogic\CheckoutAPI\PromotionalWidgets\Responses
 */
class PromotionalWidgetsCheckoutResponse extends Response
{
    /**
     * @var WidgetInitializer
     */
    protected $widgetInitializer;


    public function __construct(
        WidgetInitializer $widgetInitializer
    ) {
        $this->widgetInitializer = $widgetInitializer;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            'assetKey' => $this->widgetInitializer->getAssetKey(),
            'merchantId' => $this->widgetInitializer->getMerchantId(),
            'products' => $this->widgetInitializer->getProducts(),
            'scriptUri' => $this->widgetInitializer->getScriptUri(),
            'locale' => $this->widgetInitializer->getLocale(),
            'currency' => $this->widgetInitializer->getCurrency(),
            'decimalSeparator' => $this->widgetInitializer->getDecimalSeparator(),
            'thousandSeparator' => $this->widgetInitializer->getThousandSeparator(),
        ];
    }
}
