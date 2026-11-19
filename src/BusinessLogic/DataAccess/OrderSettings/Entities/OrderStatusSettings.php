<?php

namespace SeQura\Core\BusinessLogic\DataAccess\OrderSettings\Entities;

use SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\Exceptions\EmptyOrderStatusMappingParameterException;
use SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\Exceptions\InvalidSeQuraOrderStatusException;
use SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\Models\OrderStatusMapping;
use SeQura\Core\Infrastructure\ORM\Configuration\EntityConfiguration;
use SeQura\Core\Infrastructure\ORM\Configuration\IndexMap;
use SeQura\Core\Infrastructure\ORM\Entity;

/**
 * Class OrderStatusMapping
 *
 * @package SeQura\Core\Infrastructure\ORM\Entity
 */
class OrderStatusSettings extends Entity
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
     * @var OrderStatusMapping[]
     */
    protected $orderStatusMappings;

    /**
     * @inheritDoc
     *
     * @throws InvalidSeQuraOrderStatusException
     * @throws EmptyOrderStatusMappingParameterException
     */
    public function inflate(array $data): void
    {
        parent::inflate($data);

        $orderStatusMappings = $data['orderStatusMappings'] ?? [];

        $this->storeId = $data['storeId'] ?? '';
        foreach ($orderStatusMappings as $mapping) {
            $this->orderStatusMappings[] = new OrderStatusMapping(
                self::getArrayValue($mapping, 'sequraStatus'),
                self::getArrayValue($mapping, 'shopStatus')
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $data = parent::toArray();

        $data['storeId'] = $this->storeId;
        $data['orderStatusMappings'] = [];

        foreach ($this->orderStatusMappings as $mapping) {
            $data['orderStatusMappings'][] = [
                'sequraStatus' => $mapping->getSequraStatus(),
                'shopStatus' => $mapping->getShopStatus()
            ];
        }

        return $data;
    }

    /**
     * @inheritDoc
     */
    public function getConfig(): EntityConfiguration
    {
        $indexMap = new IndexMap();

        $indexMap->addStringIndex('storeId');

        return new EntityConfiguration($indexMap, 'OrderStatusSettings');
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
     * @return OrderStatusMapping[]
     */
    public function getOrderStatusMappings(): array
    {
        return $this->orderStatusMappings;
    }

    /**
     * @param OrderStatusMapping[] $orderStatusMappings
     */
    public function setOrderStatusMappings(array $orderStatusMappings): void
    {
        $this->orderStatusMappings = $orderStatusMappings;
    }
}
