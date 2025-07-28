<?php

namespace SeQura\Core\BusinessLogic\DataAccess\Credentials\Entities;

use Exception;
use SeQura\Core\Infrastructure\ORM\Configuration\EntityConfiguration;
use SeQura\Core\Infrastructure\ORM\Configuration\IndexMap;
use SeQura\Core\Infrastructure\ORM\Entity;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\Credentials as CredentialsDomainModel;

/**
 * Class Credentials.
 *
 * @package SeQura\Core\BusinessLogic\DataAccess\Credentials\Entities
 */
class Credentials extends Entity
{
    /**
     * Fully qualified name of this class.
     */
    public const CLASS_NAME = __CLASS__;

    /**
     * @var string $storeId
     */
    protected $storeId;
    /**
     * @var string $country
     */
    protected $country;
    /**
     * @var string $merchantId
     */
    protected $merchantId;
    /**
     * @var CredentialsDomainModel $credentials
     */
    protected $credentials;

    /**
     * @inheritDoc
     */
    public function getConfig(): EntityConfiguration
    {
        $indexMap = new IndexMap();

        $indexMap->addStringIndex('storeId');
        $indexMap->addStringIndex('country');
        $indexMap->addStringIndex('merchantId');

        return new EntityConfiguration($indexMap, 'Credentials');
    }

    /**
     * @inheritDoc
     *
     * @throws Exception
     */
    public function inflate(array $data): void
    {
        parent::inflate($data);

        $this->storeId = $data['storeId'];
        $this->country = $data['country'];
        $this->merchantId = $data['merchantId'] ?? '';
        $this->credentials = CredentialsDomainModel::fromArray($data['credentials']);
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $data = parent::toArray();

        $data['storeId'] = $this->storeId;
        $data['country'] = $this->country;
        $data['merchantId'] = $this->merchantId;
        $data['credentials'] = $this->credentials->toArray();

        return $data;
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
     *
     * @return void
     */
    public function setStoreId(string $storeId): void
    {
        $this->storeId = $storeId;
    }

    /**
     * @return string
     */
    public function getCountry(): string
    {
        return $this->country;
    }

    /**
     * @param string $country
     *
     * @return void
     */
    public function setCountry(string $country): void
    {
        $this->country = $country;
    }

    /**
     * @return CredentialsDomainModel
     */
    public function getCredentials(): CredentialsDomainModel
    {
        return $this->credentials;
    }

    /**
     * @return string
     */
    public function getMerchantId(): string
    {
        return $this->merchantId;
    }

    /**
     * @param string $merchantId
     *
     * @return void
     */
    public function setMerchantId(string $merchantId): void
    {
        $this->merchantId = $merchantId;
    }

    /**
     * @param CredentialsDomainModel $credentials
     *
     * @return void
     */
    public function setCredentials(CredentialsDomainModel $credentials): void
    {
        $this->credentials = $credentials;
    }
}
