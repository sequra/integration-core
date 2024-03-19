<?php

namespace SeQura\Core\BusinessLogic\DataAccess\GeneralSettings\Entities;

use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Models\GeneralSettings as DomainGeneralSettings;
use SeQura\Core\Infrastructure\ORM\Configuration\EntityConfiguration;
use SeQura\Core\Infrastructure\ORM\Configuration\IndexMap;
use SeQura\Core\Infrastructure\ORM\Entity;

/**
 * Class GeneralSettings
 *
 * @package SeQura\Core\BusinessLogic\DataAccess\GeneralSettings\Entities
 */
class GeneralSettings extends Entity
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
     * @var DomainGeneralSettings
     */
    protected $generalSettings;

    /**
     * @inheritDoc
     */
    public function inflate(array $data): void
    {
        parent::inflate($data);

        $generalSettings = $data['generalSettings'] ?? [];
        $this->storeId = $data['storeId'] ?? '';

        $this->generalSettings = new DomainGeneralSettings(
            self::getArrayValue($generalSettings, 'sendOrderReportsPeriodicallyToSeQura'),
            self::getArrayValue($generalSettings, 'showSeQuraCheckoutAsHostedPage'),
            static::getDataValue($generalSettings, 'allowedIPAddresses', []),
            static::getDataValue($generalSettings, 'excludedProducts', []),
            static::getDataValue($generalSettings, 'excludedCategories', [])
        );
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $data = parent::toArray();
        $data['storeId'] = $this->storeId;
        $data['generalSettings'] = [
            'sendOrderReportsPeriodicallyToSeQura' => $this->generalSettings->isSendOrderReportsPeriodicallyToSeQura(),
            'showSeQuraCheckoutAsHostedPage' => $this->generalSettings->isShowSeQuraCheckoutAsHostedPage(),
            'allowedIPAddresses' => $this->generalSettings->getAllowedIPAddresses(),
            'excludedProducts' => $this->generalSettings->getExcludedProducts(),
            'excludedCategories' => $this->generalSettings->getExcludedCategories(),
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

        return new EntityConfiguration($indexMap, 'GeneralSettings');
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
     * @return DomainGeneralSettings
     */
    public function getGeneralSettings(): DomainGeneralSettings
    {
        return $this->generalSettings;
    }

    /**
     * @param DomainGeneralSettings $generalSettings
     */
    public function setGeneralSettings(DomainGeneralSettings $generalSettings): void
    {
        $this->generalSettings = $generalSettings;
    }
}
