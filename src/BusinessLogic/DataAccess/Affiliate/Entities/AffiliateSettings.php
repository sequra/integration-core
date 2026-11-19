<?php

namespace SeQura\Core\BusinessLogic\DataAccess\Affiliate\Entities;

use SeQura\Core\Infrastructure\ORM\Configuration\EntityConfiguration;
use SeQura\Core\Infrastructure\ORM\Configuration\IndexMap;
use SeQura\Core\Infrastructure\ORM\Entity;
use SeQura\Core\BusinessLogic\Domain\Affiliate\Models\AffiliateSettings as DomainAffiliateSettings;

/**
 * Class AffiliateSettings.
 *
 * @package SeQura\Core\BusinessLogic\DataAccess\Affiliate\Entities
 */
class AffiliateSettings extends Entity
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
     * @var DomainAffiliateSettings
     */
    protected $affiliateSettings;

    /**
     * @inheritDoc
     */
    public function inflate(array $data): void
    {
        parent::inflate($data);

        $affiliateSettings = $data['affiliateSettings'] ?? [];
        $this->storeId = $data['storeId'] ?? '';

        $this->affiliateSettings = new DomainAffiliateSettings(
            (bool)self::getDataValue($affiliateSettings, 'isEnabled', false),
            (string)self::getDataValue($affiliateSettings, 'offerId', ''),
            (string)self::getDataValue($affiliateSettings, 'securityToken', '')
        );
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $data = parent::toArray();
        $data['storeId'] = $this->storeId;
        $data['affiliateSettings'] = [
            'isEnabled' => $this->affiliateSettings->isEnabled(),
            'offerId' => $this->affiliateSettings->getOfferId(),
            'securityToken' => $this->affiliateSettings->getSecurityToken()
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

        return new EntityConfiguration($indexMap, 'AffiliateSettings');
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
     * @return DomainAffiliateSettings
     */
    public function getAffiliateSettings(): DomainAffiliateSettings
    {
        return $this->affiliateSettings;
    }

    /**
     * @param DomainAffiliateSettings $affiliateSettings
     */
    public function setAffiliateSettings(DomainAffiliateSettings $affiliateSettings): void
    {
        $this->affiliateSettings = $affiliateSettings;
    }
}
