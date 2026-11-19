<?php

namespace SeQura\Core\BusinessLogic\DataAccess\BannerSettings\Entities;

use SeQura\Core\BusinessLogic\Domain\BannerSettings\Models\Banner;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Models\BannerSettings as DomainBannerSettings;
use SeQura\Core\Infrastructure\ORM\Configuration\EntityConfiguration;
use SeQura\Core\Infrastructure\ORM\Configuration\IndexMap;
use SeQura\Core\Infrastructure\ORM\Entity;

/**
 * Class BannerSettings
 *
 * @package SeQura\Core\BusinessLogic\DataAccess\BannerSettings\Entities
 */
class BannerSettings extends Entity
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
     * @var DomainBannerSettings
     */
    protected $bannerSettings;

    /**
     * @inheritDoc
     */
    public function inflate(array $data): void
    {
        parent::inflate($data);

        $bannerSettings = $data['bannerSettings'] ?? [];

        $this->storeId = $data['storeId'] ?? '';
        $this->bannerSettings = new DomainBannerSettings(
            $this->inflateBannerConfigs(static::getDataValue($bannerSettings, 'bannerConfigs', []))
        );
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $data = parent::toArray();

        $data['storeId'] = $this->storeId;
        $data['bannerSettings'] = $this->bannerSettings->toArray();

        return $data;
    }

    /**
     * @inheritDoc
     */
    public function getConfig(): EntityConfiguration
    {
        $indexMap = new IndexMap();

        $indexMap->addStringIndex('storeId');

        return new EntityConfiguration($indexMap, 'BannerSettings');
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
     * @return DomainBannerSettings
     */
    public function getBannerSettings(): DomainBannerSettings
    {
        return $this->bannerSettings;
    }

    /**
     * @param DomainBannerSettings $bannerSettings
     */
    public function setBannerSettings(DomainBannerSettings $bannerSettings): void
    {
        $this->bannerSettings = $bannerSettings;
    }

    /**
     * @param array<int,array<string>> $bannerConfigs
     *
     * @return Banner[]
     */
    protected function inflateBannerConfigs(array $bannerConfigs): array
    {
        $arrayOfBannerConfigs = [];
        foreach ($bannerConfigs as $bannerConfig) {
            $arrayOfBannerConfigs[] = new Banner(
                $bannerConfig['country'] ?? '',
                $bannerConfig['linkUrl'] ?? '',
                $bannerConfig['imageUrl'] ?? '',
                $bannerConfig['displayLocation'] ?? ''
            );
        }

        return $arrayOfBannerConfigs;
    }
}
