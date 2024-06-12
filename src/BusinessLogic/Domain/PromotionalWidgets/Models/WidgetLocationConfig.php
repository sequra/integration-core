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
     * CSS Selector for retrieving the container element where the widget should be inserted.
     *
     * @var string
     */
    private $selForDefaultLocation;

    /**
     * The locations where the widget should be displayed.
     *
     * @var WidgetLocation[]
     */
    private $customLocations;

    public function __construct(string $selForPrice, string $selForAltPrice, string $selForAltPriceTrigger, string $selForDefaultLocation, array $customLocations)
    {
        $this->selForPrice = $selForPrice;
        $this->selForAltPrice = $selForAltPrice;
        $this->selForAltPriceTrigger = $selForAltPriceTrigger;
        $this->selForDefaultLocation = $selForDefaultLocation;
        $this->customLocations = $customLocations;
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
    public function getCustomLocations(): array
    {
        return $this->customLocations;
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

    public function setCustomLocations(array $locations): void
    {
        $this->customLocations = $locations;
    }

    public function setSelForDefaultLocation(string $selForDefaultLocation): void
    {
        $this->selForDefaultLocation = $selForDefaultLocation;
    }

    public function getSelForDefaultLocation(): string
    {
        return $this->selForDefaultLocation;
    }

    public static function fromArray(array $data): ?self
    {
        if (
            !isset($data['selForPrice'])
            || !isset($data['selForAltPrice'])
            || !isset($data['selForAltPriceTrigger'])
            || !isset($data['selForDefaultLocation'])
            || !isset($data['customLocations'])
        ) {
            return null;
        }

        $locations = [];
        foreach ($data['customLocations'] as $location) {
            $location = WidgetLocation::fromArray($location);
            if ($location) {
                $locations[] = $location;
            }
        }

        return new self(
            $data['selForPrice'],
            $data['selForAltPrice'],
            $data['selForAltPriceTrigger'],
            $data['selForDefaultLocation'],
            $locations
        );
    }

    public function toArray(): array
    {
        $locations = [];
        foreach ($this->customLocations as $location) {
            $locations[] = $location->toArray();
        }

        return [
            'selForPrice' => $this->selForPrice,
            'selForAltPrice' => $this->selForAltPrice,
            'selForAltPriceTrigger' => $this->selForAltPriceTrigger,
            'selForDefaultLocation' => $this->selForDefaultLocation,
            'customLocations' => $locations
        ];
    }
}
