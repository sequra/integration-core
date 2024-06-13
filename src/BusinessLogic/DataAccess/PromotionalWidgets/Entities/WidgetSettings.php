<?php

namespace SeQura\Core\BusinessLogic\DataAccess\PromotionalWidgets\Entities;

use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\WidgetLabels as DomainWidgetLabels;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\WidgetLocationConfig;
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

        $widgetSettings = $data['widgetSettings'] ?? [];
        $widgetLabels = $widgetSettings['widgetLabels'] ?? [];

        $this->storeId = $data['storeId'] ?? '';
        $this->widgetSettings = new DomainWidgetSettings(
            self::getArrayValue($widgetSettings, 'enabled', false),
            self::getArrayValue($widgetSettings, 'assetsKey', ''),
            self::getArrayValue($widgetSettings, 'displayOnProductPage', false),
            self::getArrayValue($widgetSettings, 'showInstallmentsInProductListing', false),
            self::getArrayValue($widgetSettings, 'showInstallmentsInCartPage', false),
            self::getArrayValue($widgetSettings, 'miniWidgetSelector', ''),
            self::getArrayValue($widgetSettings, 'widgetConfiguration', ''),
            $widgetLabels ? new DomainWidgetLabels(
                static::getDataValue($widgetLabels, 'messages', []),
                static::getDataValue($widgetLabels, 'messagesBelowLimit', [])
            ) : null,
            WidgetLocationConfig::fromArray(self::getArrayValue($widgetSettings, 'widgetLocationConfiguration', []))
        );
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $data = parent::toArray();

        $labels = $this->widgetSettings->getWidgetLabels();
        $locationConfig = $this->widgetSettings->getLocationConfig();

        $data['storeId'] = $this->storeId;
        $data['widgetSettings'] = [
            'enabled' => $this->widgetSettings->isEnabled(),
            'assetsKey' => $this->widgetSettings->getAssetsKey(),
            'displayOnProductPage' => $this->widgetSettings->isDisplayOnProductPage(),
            'showInstallmentsInProductListing' => $this->widgetSettings->isShowInstallmentsInProductListing(),
            'showInstallmentsInCartPage' => $this->widgetSettings->isShowInstallmentsInCartPage(),
            'miniWidgetSelector' => $this->widgetSettings->getMiniWidgetSelector(),
            'widgetConfiguration' => $this->widgetSettings->getWidgetConfig(),
            'widgetLabels' => $labels ? [
                'messages' => $labels->getMessages(),
                'messagesBelowLimit' => $labels->getMessagesBelowLimit(),
            ] : [],
            'widgetLocationConfiguration' => $locationConfig ? $locationConfig->toArray() : [],
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
