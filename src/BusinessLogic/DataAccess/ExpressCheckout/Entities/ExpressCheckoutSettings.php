<?php

namespace SeQura\Core\BusinessLogic\DataAccess\ExpressCheckout\Entities;

use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Exceptions\DuplicatedExpressCheckoutPageException;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Exceptions\InvalidExpressCheckoutPageConfigException;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Exceptions\InvalidExpressCheckoutPageException;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Models\ExpressCheckoutPageConfig;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Models\ExpressCheckoutSettings as DomainExpressCheckoutSettings;
use SeQura\Core\Infrastructure\ORM\Configuration\EntityConfiguration;
use SeQura\Core\Infrastructure\ORM\Configuration\IndexMap;
use SeQura\Core\Infrastructure\ORM\Entity;

/**
 * Class ExpressCheckoutSettings
 *
 * @package SeQura\Core\BusinessLogic\DataAccess\ExpressCheckout\Entities
 */
class ExpressCheckoutSettings extends Entity
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
     * @var DomainExpressCheckoutSettings
     */
    protected $expressCheckoutSettings;

    /**
     * @inheritDoc
     *
     * @throws InvalidExpressCheckoutPageException|DuplicatedExpressCheckoutPageException|InvalidExpressCheckoutPageConfigException
     */
    public function inflate(array $data): void
    {
        parent::inflate($data);

        $expressCheckoutSettings = $data['expressCheckoutSettings'] ?? [];
        $this->storeId = $data['storeId'] ?? '';
        $rawConfigs = static::getDataValue($expressCheckoutSettings, 'expressCheckoutConfigs', []);
        $configs = [];

        foreach ($rawConfigs as $configData) {
            if (\is_array($configData)) {
                $configs[] = ExpressCheckoutPageConfig::fromArray($configData);
            }
        }

        $this->expressCheckoutSettings = new DomainExpressCheckoutSettings($configs);
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $data = parent::toArray();
        $data['storeId'] = $this->storeId;
        $data['expressCheckoutSettings'] = [
            'expressCheckoutConfigs' => array_map(static function (ExpressCheckoutPageConfig $config) {
                return $config->toArray();
            }, $this->expressCheckoutSettings->getExpressCheckoutConfigs()),
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

        return new EntityConfiguration($indexMap, 'ExpressCheckoutSettings');
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
     * @return DomainExpressCheckoutSettings
     */
    public function getExpressCheckoutSettings(): DomainExpressCheckoutSettings
    {
        return $this->expressCheckoutSettings;
    }

    /**
     * @param DomainExpressCheckoutSettings $expressCheckoutSettings
     */
    public function setExpressCheckoutSettings(DomainExpressCheckoutSettings $expressCheckoutSettings): void
    {
        $this->expressCheckoutSettings = $expressCheckoutSettings;
    }
}
