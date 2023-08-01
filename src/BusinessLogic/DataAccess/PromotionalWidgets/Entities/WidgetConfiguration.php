<?php

namespace SeQura\Core\BusinessLogic\DataAccess\PromotionalWidgets\Entities;

use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\WidgetConfiguration as DomainWidgetConfiguration;
use SeQura\Core\Infrastructure\ORM\Configuration\EntityConfiguration;
use SeQura\Core\Infrastructure\ORM\Configuration\IndexMap;
use SeQura\Core\Infrastructure\ORM\Entity;

/**
 * Class WidgetConfiguration
 *
 * @package SeQura\Core\BusinessLogic\DataAccess\PromotionalWidgets\Entities
 */
class WidgetConfiguration extends Entity
{
    /**
     * Fully qualified name of this class.
     */
    public const CLASS_NAME = __CLASS__;
    /**
     * @var string
     */
    protected $storeId;
    /**
     * @var DomainWidgetConfiguration
     */
    protected $widgetConfig;

    /**
     * @inheritDoc
     */
    public function inflate(array $data)
    {
        parent::inflate($data);

        $widgetConfiguration = $data['widgetConfiguration'] ?? [];

        $this->storeId = $data['storeId'] ?? '';
        $this->widgetConfig = new DomainWidgetConfiguration(
            self::getArrayValue($widgetConfiguration, 'type', ''),
            self::getArrayValue($widgetConfiguration, 'size', ''),
            self::getArrayValue($widgetConfiguration, 'fontColor', ''),
            self::getArrayValue($widgetConfiguration, 'backgroundColor', ''),
            self::getArrayValue($widgetConfiguration, 'alignment', ''),
            self::getArrayValue($widgetConfiguration, 'branding', ''),
            self::getArrayValue($widgetConfiguration, 'startingText', ''),
            self::getArrayValue($widgetConfiguration, 'amountFontSize', ''),
            self::getArrayValue($widgetConfiguration, 'amountFontColor', ''),
            self::getArrayValue($widgetConfiguration, 'amountFontBold', ''),
            self::getArrayValue($widgetConfiguration, 'linkFontColor', ''),
            self::getArrayValue($widgetConfiguration, 'linkUnderline', ''),
            self::getArrayValue($widgetConfiguration, 'borderColor', ''),
            self::getArrayValue($widgetConfiguration, 'borderRadius', ''),
            self::getArrayValue($widgetConfiguration, 'noCostsClaim', '')
        );
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $data = parent::toArray();
        $data['storeId'] = $this->storeId;
        $data['widgetConfiguration'] = [
            'type' => $this->widgetConfig->getType(),
            'size' => $this->widgetConfig->getSize(),
            'fontColor' => $this->widgetConfig->getFontColor(),
            'backgroundColor' => $this->widgetConfig->getBackgroundColor(),
            'alignment' => $this->widgetConfig->getAlignment(),
            'branding' => $this->widgetConfig->getBranding(),
            'startingText' => $this->widgetConfig->getStartingText(),
            'amountFontSize' => $this->widgetConfig->getAmountFontSize(),
            'amountFontColor' => $this->widgetConfig->getAmountFontColor(),
            'amountFontBold' => $this->widgetConfig->getAmountFontBold(),
            'linkFontColor' => $this->widgetConfig->getLinkFontColor(),
            'linkUnderline' => $this->widgetConfig->getLinkUnderline(),
            'borderColor' => $this->widgetConfig->getBorderColor(),
            'borderRadius' => $this->widgetConfig->getBorderRadius(),
            'noCostsClaim' => $this->widgetConfig->getNoCostsClaim(),
        ];

        return $data;
    }

    /**
     * @inheritDoc
     */
    public function getConfig(): EntityConfiguration
    {
        $indexMap = new IndexMap();

        $indexMap->addStringIndex('storeId');

        return new EntityConfiguration($indexMap, 'WidgetConfiguration');
    }

    /**
     * @return string
     */
    public function getStoreId(): string
    {
        return $this->storeId;
    }

    /**
     * @param string $storeId
     */
    public function setStoreId(string $storeId): void
    {
        $this->storeId = $storeId;
    }

    /**
     * @return DomainWidgetConfiguration
     */
    public function getWidgetConfig(): DomainWidgetConfiguration
    {
        return $this->widgetConfig;
    }

    /**
     * @param DomainWidgetConfiguration $widgetConfig
     */
    public function setWidgetConfig(DomainWidgetConfiguration $widgetConfig): void
    {
        $this->widgetConfig = $widgetConfig;
    }
}