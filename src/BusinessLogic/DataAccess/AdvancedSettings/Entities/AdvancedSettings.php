<?php

namespace SeQura\Core\BusinessLogic\DataAccess\AdvancedSettings\Entities;

use SeQura\Core\Infrastructure\ORM\Configuration\EntityConfiguration;
use SeQura\Core\Infrastructure\ORM\Configuration\IndexMap;
use SeQura\Core\Infrastructure\ORM\Entity;
use SeQura\Core\BusinessLogic\Domain\AdvancedSettings\Models\AdvancedSettings as DomainAdvancedSettings;

/**
 * Class AdvancedSettings.
 *
 * @package SeQura\Core\BusinessLogic\DataAccess\AdvancedSettings\Entities
 */
class AdvancedSettings extends Entity
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
     * @var DomainAdvancedSettings
     */
    protected $advancedSettings;

    /**
     * @inheritDoc
     */
    public function inflate(array $data): void
    {
        parent::inflate($data);

        $generalSettings = $data['advancedSettings'] ?? [];
        $this->storeId = $data['storeId'] ?? '';

        $this->advancedSettings = new DomainAdvancedSettings(
            (bool)self::getDataValue($generalSettings, 'isEnabled', false),
            (int)self::getDataValue($generalSettings, 'level', 1)
        );
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $data = parent::toArray();
        $data['storeId'] = $this->storeId;
        $data['advancedSettings'] = [
            'isEnabled' => $this->advancedSettings->isEnabled(),
            'level' => $this->advancedSettings->getLevel()
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

        return new EntityConfiguration($indexMap, 'AdvancedSettings');
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
     * @return DomainAdvancedSettings
     */
    public function getAdvancedSettings(): DomainAdvancedSettings
    {
        return $this->advancedSettings;
    }

    /**
     * @param DomainAdvancedSettings $advancedSettings
     */
    public function setAdvancedSettings(DomainAdvancedSettings $advancedSettings): void
    {
        $this->advancedSettings = $advancedSettings;
    }
}
