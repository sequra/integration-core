<?php

namespace SeQura\Core\BusinessLogic\DataAccess\OrderSettings\Entities;

use SeQura\Core\Infrastructure\ORM\Configuration\EntityConfiguration;
use SeQura\Core\Infrastructure\ORM\Configuration\IndexMap;
use SeQura\Core\Infrastructure\ORM\Entity;

/**
 * Class OrderStatusMapping
 *
 * @package SeQura\Core\Infrastructure\ORM\Entity
 */
class OrderStatusMapping extends Entity
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
     * @var array
     */
    protected $orderStatusMappingSettings;

    /**
     * @inheritDoc
     */
    public function inflate(array $data): void
    {
        parent::inflate($data);

        $this->storeId = $data['storeId'] ?? '';
        $this->orderStatusMappingSettings = $data['orderStatusMapping'] ?? [];
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $data = parent::toArray();

        $data['storeId'] = $this->storeId;
        $data['orderStatusMapping'] = $this->orderStatusMappingSettings;

        return $data;
    }

    /**
     * @inheritDoc
     */
    public function getConfig(): EntityConfiguration
    {
        $indexMap = new IndexMap();

        $indexMap->addStringIndex('storeId');

        return new EntityConfiguration($indexMap, 'OrderStatusMappings');
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
     * @return array
     */
    public function getOrderStatusMappingSettings(): array
    {
        return $this->orderStatusMappingSettings;
    }

    /**
     * @param array $orderStatusMappingSettings
     */
    public function setOrderStatusMappingSettings(array $orderStatusMappingSettings): void
    {
        $this->orderStatusMappingSettings = $orderStatusMappingSettings;
    }
}
