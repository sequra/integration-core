<?php

namespace SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models;

use SeQura\Core\Infrastructure\Data\DataTransferObject;

/**
 * Class DefaultWidgetSettings
 *
 * @package SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models
 */
class DefaultWidgetSettings extends DataTransferObject
{
    /**
     * @var string
     */
    protected $productPriceSelector;
    /**
     * @var string
     */
    protected $defaultProductLocationSelector;
    /**
     * @var string
     */
    protected $altProductPriceSelector;
    /**
     * @var string
     */
    protected $altProductPriceTriggerSelector;
    /**
     * @var string
     */
    protected $cartPriceSelector;
    /**
     * @var string
     */
    protected $cartLocationSelector;
    /**
     * @var string
     */
    protected $listingPriceSelector;
    /**
     * @var string
     */
    protected $listingLocationSelector;

    public function __construct(
        string $productPriceSelector,
        string $defaultProductLocationSelector,
        string $altProductPriceSelector,
        string $altProductPriceTriggerSelector,
        string $cartPriceSelector,
        string $cartLocationSelector,
        string $listingPriceSelector,
        string $listingLocationSelector
    ) {
        $this->productPriceSelector = $productPriceSelector;
        $this->defaultProductLocationSelector = $defaultProductLocationSelector;
        $this->altProductPriceSelector = $altProductPriceSelector;
        $this->altProductPriceTriggerSelector = $altProductPriceTriggerSelector;
        $this->cartPriceSelector = $cartPriceSelector;
        $this->cartLocationSelector = $cartLocationSelector;
        $this->listingPriceSelector = $listingPriceSelector;
        $this->listingLocationSelector = $listingLocationSelector;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            'productPriceSelector' => $this->productPriceSelector,
            'defaultProductLocationSelector' => $this->defaultProductLocationSelector,
            'altProductPriceSelector' => $this->altProductPriceSelector,
            'altProductPriceTriggerSelector' => $this->altProductPriceTriggerSelector,
            'cartPriceSelector' => $this->cartPriceSelector,
            'cartLocationSelector' => $this->cartLocationSelector,
            'listingPriceSelector' => $this->listingPriceSelector,
            'listingLocationSelector' => $this->listingLocationSelector,
        ];
    }
}
