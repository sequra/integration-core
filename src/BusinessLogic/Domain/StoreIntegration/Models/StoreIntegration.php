<?php

namespace SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models;

use SeQura\Core\Infrastructure\Data\DataTransferObject;

/**
 * Class StoreIntegration
 *
 * @package SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models
 */
class StoreIntegration extends DataTransferObject
{
    /** @var string  */
    protected $storeId;

    /** @var string  */
    protected $signature;

    /** @var string  */
    protected $integrationId;

    /**
     * @param string $storeId
     * @param string $signature
     * @param string $integrationId
     */
    public function __construct(string $storeId, string $signature, string $integrationId)
    {
        $this->storeId = $storeId;
        $this->signature = $signature;
        $this->integrationId = $integrationId;
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
     * @return void
     */
    public function setStoreId(string $storeId): void
    {
        $this->storeId = $storeId;
    }

    /**
     * @return string
     */
    public function getSignature(): string
    {
        return $this->signature;
    }

    /**
     * @param string $signature
     * @return void
     */
    public function setSignature(string $signature): void
    {
        $this->signature = $signature;
    }

    public function getIntegrationId(): string
    {
        return $this->integrationId;
    }

    public function setIntegrationId(string $integrationId): void
    {
        $this->integrationId = $integrationId;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $data['storeIntegration'] = [
            'storeId' => $this->storeId,
            'signature' => $this->signature,
            'integrationId' => $this->integrationId
        ];

        return $data;
    }
}
