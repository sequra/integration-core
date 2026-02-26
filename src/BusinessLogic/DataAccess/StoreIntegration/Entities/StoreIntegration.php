<?php

namespace SeQura\Core\BusinessLogic\DataAccess\StoreIntegration\Entities;

use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models\StoreIntegration as DomainStoreIntegration;
use SeQura\Core\BusinessLogic\Utility\EncryptorInterface;
use SeQura\Core\Infrastructure\ORM\Configuration\EntityConfiguration;
use SeQura\Core\Infrastructure\ORM\Configuration\IndexMap;
use SeQura\Core\Infrastructure\ORM\Entity;
use SeQura\Core\Infrastructure\ServiceRegister;

/**
 * Class StoreIntegration.
 *
 * @package SeQura\Core\BusinessLogic\DataAccess\StoreIntegration\Entities
 */
class StoreIntegration extends Entity
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
     * @var DomainStoreIntegration
     */
    protected $storeIntegration;

    /**
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * @inheritDoc
     */
    public function inflate(array $data): void
    {
        parent::inflate($data);

        $storeIntegration = $data['storeIntegration'] ?? [];

        $this->storeId = $data['storeId'] ?? '';
        $this->storeIntegration = new DomainStoreIntegration(
            self::getArrayValue($storeIntegration, 'storeId'),
            $this->getEncryptorUtility()->decrypt(
                self::getArrayValue($storeIntegration, 'signature')
            ),
            self::getArrayValue($storeIntegration, 'integrationId'),
            self::getArrayValue($storeIntegration, 'webhookUrl')
        );
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $data = parent::toArray();

        $data['storeId'] = $this->storeId;
        $data['storeIntegration'] = [
            'storeId' => $this->storeIntegration->getStoreId(),
            'signature' => $this->getEncryptorUtility()->encrypt(
                $this->storeIntegration->getSignature()
            ),
            'integrationId' => $this->storeIntegration->getIntegrationId(),
            'webhookUrl' => $this->storeIntegration->getWebhookUrl(),
        ];

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
     * @return DomainStoreIntegration
     */
    public function getStoreIntegration(): DomainStoreIntegration
    {
        return $this->storeIntegration;
    }

    /**
     * @param DomainStoreIntegration $storeIntegration
     *
     * @return void
     */
    public function setStoreIntegration(DomainStoreIntegration $storeIntegration): void
    {
        $this->storeIntegration = $storeIntegration;
    }

    /**
     * @inheritDoc
     */
    public function getConfig(): EntityConfiguration
    {
        $indexMap = new IndexMap();
        $indexMap->addStringIndex('storeId');

        return new EntityConfiguration($indexMap, 'StoreIntegration');
    }

    /**
     * Gets Encryptor instance.
     *
     * @return EncryptorInterface Encryptor instance.
     */
    protected function getEncryptorUtility(): EncryptorInterface
    {
        if ($this->encryptor === null) {
            $this->encryptor = ServiceRegister::getService(EncryptorInterface::class);
        }

        return $this->encryptor;
    }
}
