<?php

namespace SeQura\Core\BusinessLogic\DataAccess\PromotionalWidgets\Entities;

use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\WidgetSettings as DomainWidgetSettings;
use SeQura\Core\Infrastructure\ORM\Configuration\EntityConfiguration;
use SeQura\Core\Infrastructure\ORM\Configuration\IndexMap;
use SeQura\Core\Infrastructure\ORM\Entity;

/**
 * Class WidgetSettings
 *
 * @package SeQura\Core\BusinessLogic\DataAccess\PromotionalWidgets\Entities
 */
class WidgetSettings extends Entity
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
     * @var DomainWidgetSettings
     */
    protected $widgetSettings;

    /**
     * @inheritDoc
     */
    public function inflate(array $data)
    {
        parent::inflate($data);

        $widgetConfiguration = $data['widgetConfiguration'] ?? [];

        $this->storeId = $data['storeId'] ?? '';
        $this->widgetSettings = new DomainWidgetSettings(
            self::getArrayValue($widgetConfiguration, 'enabled', false),
            self::getArrayValue($widgetConfiguration, 'assetsKey', ''),
            self::getArrayValue($widgetConfiguration, 'displayOnProductPage', false),
            self::getArrayValue($widgetConfiguration, 'showInProductListing', false),
            self::getArrayValue($widgetConfiguration, 'showInCartPage', false)
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
            'enabled' => $this->widgetSettings->isEnabled(),
            'assetsKey' => $this->widgetSettings->getAssetsKey(),
            'displayOnProductPage' => $this->widgetSettings->isDisplayOnProductPage(),
            'showInProductListing' => $this->widgetSettings->isShowInProductListing(),
            'showInCartPage' => $this->widgetSettings->isShowInCartPage(),
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

        return new EntityConfiguration($indexMap, 'WidgetSettings');
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
     * @return DomainWidgetSettings
     */
    public function getWidgetSettings(): DomainWidgetSettings
    {
        return $this->widgetSettings;
    }

    /**
     * @param DomainWidgetSettings $widgetSettings
     */
    public function setWidgetSettings(DomainWidgetSettings $widgetSettings): void
    {
        $this->widgetSettings = $widgetSettings;
    }
}