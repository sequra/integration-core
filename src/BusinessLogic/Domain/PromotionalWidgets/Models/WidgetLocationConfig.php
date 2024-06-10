<?php

namespace SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models;

/**
 * Class WidgetLocationConfig
 *
 * @package SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models
 */
class WidgetLocationConfig
{
    /**
     * CSS selector for retrieving the price element.
     *
     * @var string
     */
    private $selForPrice;

     /**
     * CSS selector for retrieving the price element from an alternative location.
     * Intended for cases where the product layout changes for some products.
     *
     * @var string
     */
    private $selForAltPrice;

     /**
     * CSS Selector for detecting when to use the alternative price selector.
     *
     * @var string
     */
    private $selForAltPriceTrigger;

    /**
     * The locations where the widget should be displayed.
     *
     * @var WidgetLocation[]
     */
    private $locations;

    public function __construct(string $selForPrice, string $selForAltPrice, string $selForAltPriceTrigger, array $locations)
    {
        $this->selForPrice = $selForPrice;
        $this->selForAltPrice = $selForAltPrice;
        $this->selForAltPriceTrigger = $selForAltPriceTrigger;
        $this->locations = $locations;
    }

    public function getSelForPrice(): string
    {
        return $this->selForPrice;
    }

    public function getSelForAltPrice(): string
    {
        return $this->selForAltPrice;
    }

    public function getSelForAltPriceTrigger(): string
    {
        return $this->selForAltPriceTrigger;
    }

    /**
     * @return WidgetLocation[]
     */
    public function getLocations(): array
    {
        return $this->locations;
    }

    public function setSelForPrice(string $selForPrice): void
    {
        $this->selForPrice = $selForPrice;
    }

    public function setSelForAltPrice(string $selForAltPrice): void
    {
        $this->selForAltPrice = $selForAltPrice;
    }

    public function setSelForAltPriceTrigger(string $selForAltPriceTrigger): void
    {
        $this->selForAltPriceTrigger = $selForAltPriceTrigger;
    }

    public function setLocations(array $locations): void
    {
        $this->locations = $locations;
    }

    public static function fromArray(array $data): ?self
    {
        if (
            !isset($data['selForPrice'])
            || !isset($data['selForAltPrice'])
            || !isset($data['selForAltPriceTrigger'])
            || !isset($data['locations'])
        ) {
            return null;
        }

        $locations = [];
        foreach ($data['locations'] as $location) {
            $location = WidgetLocation::fromArray($location);
            if ($location) {
                $locations[] = $location;
            }
        }

        if (empty($locations)) {
            // At least one location is required with the default selector.
            return null;
        }

        return new self(
            $data['selForPrice'],
            $data['selForAltPrice'],
            $data['selForAltPriceTrigger'],
            $locations
        );
    }

    public function toArray(): array
    {
        $locations = [];
        foreach ($this->locations as $location) {
            $locations[] = $location->toArray();
        }

        return [
            'selForPrice' => $this->selForPrice,
            'selForAltPrice' => $this->selForAltPrice,
            'selForAltPriceTrigger' => $this->selForAltPriceTrigger,
            'locations' => $locations
        ];
    }

    /**
     * Get the default location. That is, the location where only the CSS selector is defined.
     */
    public function getDefaultLocation(): ?WidgetLocation
    {
        foreach ($this->locations as $location) {
            if ($location->isDefaultLocation()) {
                return $location;
            }
        }

        return null;
    }
}
