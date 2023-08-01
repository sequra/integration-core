<?php

namespace SeQura\Core\BusinessLogic\DataAccess\PromotionalWidgets\Entities;

use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\WidgetLabels as DomainWidgetLabels;
use SeQura\Core\Infrastructure\ORM\Configuration\EntityConfiguration;
use SeQura\Core\Infrastructure\ORM\Configuration\IndexMap;
use SeQura\Core\Infrastructure\ORM\Entity;

/**
 * Class WidgetLabels
 *
 * @package SeQura\Core\BusinessLogic\DataAccess\PromotionalWidgets\Entities
 */
class WidgetLabels extends Entity
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
     * @var DomainWidgetLabels
     */
    protected $widgetLabels;

    /**
     * @inheritDoc
     */
    public function inflate(array $data)
    {
        parent::inflate($data);

        $widgetLabels = $data['widgetLabels'] ?? [];

        $this->storeId = $data['storeId'] ?? '';
        $this->widgetLabels = new DomainWidgetLabels(
            static::getDataValue($widgetLabels, 'messages', []),
            static::getDataValue($widgetLabels, 'messagesBelowLimit', [])
        );
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $data = parent::toArray();
        $data['storeId'] = $this->storeId;
        $data['widgetLabels'] = [
            'messages' => $this->widgetLabels->getMessages(),
            'messagesBelowLimit' => $this->widgetLabels->getMessagesBelowLimit(),
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

        return new EntityConfiguration($indexMap, 'WidgetLabels');
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
     * @return DomainWidgetLabels
     */
    public function getWidgetLabels(): DomainWidgetLabels
    {
        return $this->widgetLabels;
    }

    /**
     * @param DomainWidgetLabels $widgetConfig
     */
    public function setWidgetLabels(DomainWidgetLabels $widgetConfig): void
    {
        $this->widgetLabels = $widgetConfig;
    }
}