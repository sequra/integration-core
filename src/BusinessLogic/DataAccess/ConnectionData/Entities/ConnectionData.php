<?php

namespace SeQura\Core\BusinessLogic\DataAccess\ConnectionData\Entities;

use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidEnvironmentException;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\AuthorizationCredentials;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\ConnectionData as DomainConnectionData;
use SeQura\Core\BusinessLogic\Utility\EncryptorInterface;
use SeQura\Core\Infrastructure\ORM\Configuration\EntityConfiguration;
use SeQura\Core\Infrastructure\ORM\Configuration\IndexMap;
use SeQura\Core\Infrastructure\ORM\Entity;
use SeQura\Core\Infrastructure\ServiceRegister;

/**
 * Class ConnectionData
 *
 * @package SeQura\Core\BusinessLogic\DataAccess\ConnectionData\Entities
 */
class ConnectionData extends Entity
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
     * @var DomainConnectionData
     */
    protected $connectionData;
    /**
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * @inheritDoc
     *
     * @throws InvalidEnvironmentException
     */
    public function inflate(array $data): void
    {
        parent::inflate($data);

        $connectionData = $data['connectionData'] ?? [];

        $this->storeId = $data['storeId'] ?? '';
        $this->connectionData = new DomainConnectionData(
            self::getArrayValue($connectionData, 'environment'),
            self::getArrayValue($connectionData, 'merchantId'),
            new AuthorizationCredentials(
                self::getArrayValue($connectionData['authorizationCredentials'], 'username'),
                $this->getEncryptorUtility()->decrypt(
                    self::getArrayValue($connectionData['authorizationCredentials'], 'password')
                )
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $data = parent::toArray();

        $data['storeId'] = $this->storeId;
        $data['connectionData'] = [
            'environment' => $this->connectionData->getEnvironment(),
            'merchantId' => $this->connectionData->getMerchantId(),
            'authorizationCredentials' => [
                'username' => $this->connectionData->getAuthorizationCredentials()->getUsername(),
                'password' => $this->getEncryptorUtility()->encrypt(
                    $this->connectionData->getAuthorizationCredentials()->getPassword()
                )
            ]
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

        return new EntityConfiguration($indexMap, 'ConnectionData');
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
     * @return DomainConnectionData
     */
    public function getConnectionData(): DomainConnectionData
    {
        return $this->connectionData;
    }

    /**
     * @param DomainConnectionData $connectionData
     */
    public function setConnectionData(DomainConnectionData $connectionData): void
    {
        $this->connectionData = $connectionData;
    }

    /**
     * Gets Encryptor instance.
     *
     * @return EncryptorInterface Encryptor instance.
     */
    protected function getEncryptorUtility(): EncryptorInterface
    {
        if (empty($this->encryptor)) {
            $this->encryptor = ServiceRegister::getService(EncryptorInterface::class);
        }

        return $this->encryptor;
    }
}
