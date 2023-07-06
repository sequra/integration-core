<?php

namespace SeQura\Core\BusinessLogic\DataAccess\CountryConfiguration\Entities;

use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Exceptions\EmptyCountryConfigurationParameterException;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Exceptions\InvalidCountryCodeForConfigurationException;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Models\CountryConfiguration as DomainCountryConfiguration;
use SeQura\Core\Infrastructure\ORM\Configuration\EntityConfiguration;
use SeQura\Core\Infrastructure\ORM\Configuration\IndexMap;
use SeQura\Core\Infrastructure\ORM\Entity;

/**
 * Class CountryConfiguration
 *
 * @package SeQura\Core\BusinessLogic\DataAccess\CountryConfiguration\Entities
 */
class CountryConfiguration extends Entity
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
     * @var DomainCountryConfiguration[]
     */
    protected $countryConfigurations;

    /**
     * @inheritDoc
     *
     * @throws InvalidCountryCodeForConfigurationException
     * @throws EmptyCountryConfigurationParameterException
     */
    public function inflate(array $data): void
    {
        parent::inflate($data);

        $countryConfigurations = $data['countryConfigurations'] ?? [];

        $this->storeId = $data['storeId'] ?? '';
        foreach ($countryConfigurations as $configuration) {
            $this->countryConfigurations[] = new DomainCountryConfiguration(
                self::getArrayValue($configuration, 'countryCode'),
                self::getArrayValue($configuration, 'merchantId')
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
        $data['countryConfigurations'] = [];

        foreach ($this->countryConfigurations as $configuration) {
            $data['countryConfigurations'][] = [
                'countryCode' => $configuration->getCountryCode(),
                'merchantId' => $configuration->getMerchantId()
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

        return new EntityConfiguration($indexMap, 'CountryConfiguration');
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
     * @return DomainCountryConfiguration[]
     */
    public function getCountryConfiguration(): array
    {
        return $this->countryConfigurations;
    }

    /**
     * @param DomainCountryConfiguration[] $countryConfigurations
     */
    public function setCountryConfiguration(array $countryConfigurations): void
    {
        $this->countryConfigurations = $countryConfigurations;
    }
}
