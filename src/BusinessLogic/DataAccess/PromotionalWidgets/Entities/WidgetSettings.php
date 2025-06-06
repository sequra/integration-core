<?php

namespace SeQura\Core\BusinessLogic\DataAccess\PromotionalWidgets\Entities;

use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\CustomWidgetsSettings;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\WidgetSelectorSettings;
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
    public function inflate(array $data): void
    {
        parent::inflate($data);

        $widgetSettings = $data['widgetSettings'] ?? [];

        $this->storeId = $data['storeId'] ?? '';
        $this->widgetSettings = new DomainWidgetSettings(
            (bool)self::getArrayValue($widgetSettings, 'enabled', false),
            (bool)self::getArrayValue($widgetSettings, 'displayOnProductPage', false),
            (bool)self::getArrayValue($widgetSettings, 'showInstallmentsInProductListing', false),
            (bool)self::getArrayValue($widgetSettings, 'showInstallmentsInCartPage', false),
            self::getArrayValue($widgetSettings, 'widgetConfiguration', ''),
            !empty($widgetSettings['widgetSettingsForProduct']) ?
                $this->inflateWidgetSettingsForProductModel($widgetSettings['widgetSettingsForProduct']) : null,
            !empty($widgetSettings['widgetSettingsForCart']) ?
                $this->inflateWidgetSettingsForCartModel($widgetSettings['widgetSettingsForCart']) : null,
            !empty($widgetSettings['widgetSettingsForListing']) ?
                $this->inflateWidgetSettingsForProductListingModel($widgetSettings['widgetSettingsForListing']) : null
        );
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $data = parent::toArray();

        $widgetSettingsForProduct = $this->widgetSettings->getWidgetSettingsForProduct();
        $widgetSettingsForCart = $this->widgetSettings->getWidgetSettingsForCart();
        $widgetSettingsForListing = $this->widgetSettings->getWidgetSettingsForListing();

        $data['storeId'] = $this->storeId;
        $data['widgetSettings'] = [
            'enabled' => $this->widgetSettings->isEnabled(),
            'displayOnProductPage' => $this->widgetSettings->isDisplayOnProductPage(),
            'showInstallmentsInProductListing' => $this->widgetSettings->isShowInstallmentsInProductListing(),
            'showInstallmentsInCartPage' => $this->widgetSettings->isShowInstallmentsInCartPage(),
            'widgetConfiguration' => $this->widgetSettings->getWidgetConfig(),
            'widgetSettingsForProduct' => $widgetSettingsForProduct ?
                $this->widgetSettingsForProductModelArray($widgetSettingsForProduct) : [],
            'widgetSettingsForCart' => $widgetSettingsForCart ? [
                'priceSelector' => $widgetSettingsForCart->getPriceSelector(),
                'locationSelector' => $widgetSettingsForCart->getLocationSelector(),
                'widgetProduct' => $widgetSettingsForCart->getWidgetProduct()
            ] : [],
            'widgetSettingsForListing' => $widgetSettingsForListing ? [
                'priceSelector' => $widgetSettingsForListing->getPriceSelector(),
                'locationSelector' => $widgetSettingsForListing->getLocationSelector(),
                'widgetProduct' => $widgetSettingsForListing->getWidgetProduct()
            ] : []
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

    /**
     * @param mixed[] $widgetSettingsForProduct
     *
     * @return WidgetSelectorSettings
     */
    protected function inflateWidgetSettingsForProductModel(array $widgetSettingsForProduct): WidgetSelectorSettings
    {
        $widgetSettingsForProductModel = new WidgetSelectorSettings(
            static::getDataValue($widgetSettingsForProduct, 'priceSelector', ''),
            static::getDataValue($widgetSettingsForProduct, 'locationSelector', ''),
            '',
            static::getDataValue($widgetSettingsForProduct, 'altPriceSelector', ''),
            static::getDataValue($widgetSettingsForProduct, 'altPriceTriggerSelector', '')
        );

        $customWidgetSettings = static::getDataValue($widgetSettingsForProduct, 'customWidgetSettings', []);
        $arrayOfCustomWidgetsSettings = [];
        foreach ($customWidgetSettings as $customWidgetSetting) {
            $arrayOfCustomWidgetsSettings [] = new CustomWidgetsSettings(
                $customWidgetSetting['customLocationSelector'],
                $customWidgetSetting['product'],
                $customWidgetSetting['displayWidget'],
                $customWidgetSetting['customWidgetStyle']
            );
        }

        $widgetSettingsForProductModel->setCustomWidgetsSettings($arrayOfCustomWidgetsSettings);

        return $widgetSettingsForProductModel;
    }

    /**
     * @param mixed[] $widgetSettingsForCart
     *
     * @return WidgetSelectorSettings
     */
    protected function inflateWidgetSettingsForCartModel(array $widgetSettingsForCart): WidgetSelectorSettings
    {
        return new WidgetSelectorSettings(
            static::getDataValue($widgetSettingsForCart, 'priceSelector', ''),
            static::getDataValue($widgetSettingsForCart, 'locationSelector', ''),
            static::getDataValue($widgetSettingsForCart, 'widgetProduct', '')
        );
    }

    /**
     * @param mixed[] $widgetSettingsForListing
     *
     * @return WidgetSelectorSettings
     */
    protected function inflateWidgetSettingsForProductListingModel(array $widgetSettingsForListing): WidgetSelectorSettings
    {
        return new WidgetSelectorSettings(
            static::getDataValue($widgetSettingsForListing, 'priceSelector', ''),
            static::getDataValue($widgetSettingsForListing, 'locationSelector', ''),
            static::getDataValue($widgetSettingsForListing, 'widgetProduct', '')
        );
    }

    /**
     * @param WidgetSelectorSettings $widgetSettingsForProduct
     *
     * @return mixed[]
     */
    protected function widgetSettingsForProductModelArray(WidgetSelectorSettings $widgetSettingsForProduct): array
    {
        $widgetSettingsForProductArray = [
            'priceSelector' => $widgetSettingsForProduct->getPriceSelector(),
            'locationSelector' => $widgetSettingsForProduct->getLocationSelector(),
            'altPriceSelector' => $widgetSettingsForProduct->getAltPriceSelector(),
            'altPriceTriggerSelector' => $widgetSettingsForProduct->getAltPriceTriggerSelector(),
            'customWidgetSettings' => []
        ];

        foreach ($widgetSettingsForProduct->getCustomWidgetsSettings() as $customWidgetSetting) {
            $widgetSettingsForProductArray ['customWidgetSettings'][] = [
                'customLocationSelector' => $customWidgetSetting->getCustomLocationSelector(),
                'product' => $customWidgetSetting->getProduct(),
                'displayWidget' => $customWidgetSetting->isDisplayWidget(),
                'customWidgetStyle' => $customWidgetSetting->getCustomWidgetStyle()
            ];
        }

        return $widgetSettingsForProductArray;
    }
}
