<?php

namespace SeQura\Core\BusinessLogic\DataAccess\StatisticalData\Entities;

use SeQura\Core\BusinessLogic\Domain\StatisticalData\Models\StatisticalData as DomainStatisticalData;
use SeQura\Core\Infrastructure\ORM\Configuration\EntityConfiguration;
use SeQura\Core\Infrastructure\ORM\Configuration\IndexMap;
use SeQura\Core\Infrastructure\ORM\Entity;

/**
 * Class StatisticalData
 *
 * @package SeQura\Core\BusinessLogic\DataAccess\StatisticalData\Entities
 */
class StatisticalData extends Entity
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
     * @var DomainStatisticalData
     */
    protected $statisticalData;

    /**
     * @inheritDoc
     */
    public function inflate(array $data): void
    {
        parent::inflate($data);

        $statisticalData = $data['statisticalData'] ?? [];

        $this->storeId = $data['storeId'] ?? '';
        $this->statisticalData = new DomainStatisticalData(
            self::getArrayValue($statisticalData, 'sendStatisticalData')
        );
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $data = parent::toArray();
        $data['storeId'] = $this->storeId;
        $data['statisticalData'] = [
            'sendStatisticalData' => $this->statisticalData->isSendStatisticalData(),
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

        return new EntityConfiguration($indexMap, 'StatisticalData');
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
     * @return DomainStatisticalData
     */
    public function getStatisticalData(): DomainStatisticalData
    {
        return $this->statisticalData;
    }

    /**
     * @param DomainStatisticalData $statisticalData
     */
    public function setStatisticalData(DomainStatisticalData $statisticalData): void
    {
        $this->statisticalData = $statisticalData;
    }
}
