<?php

namespace SeQura\Core\BusinessLogic\DataAccess\TransactionLog\Entities;

use SeQura\Core\Infrastructure\ORM\Configuration\EntityConfiguration;
use SeQura\Core\Infrastructure\ORM\Configuration\IndexMap;
use SeQura\Core\Infrastructure\ORM\Entity;

/**
 * Class TransactionLog
 *
 * @package SeQura\Core\BusinessLogic\DataAccess\TransactionLog\Entities
 */
class TransactionLog extends Entity
{
    /**
     * Fully qualified name of this class.
     */
    public const CLASS_NAME = __CLASS__;

    /**
     * @var string
     */
    protected $storeId = '';

    /**
     * @var string
     */
    protected $merchantReference;

    /**
     * @var int
     */
    protected $executionId;

    /**
     * @var string
     */
    protected $paymentMethod;

    /**
     * @var int
     */
    protected $timestamp;

    /**
     * @var string
     */
    protected $eventCode;

    /**
     * @var bool
     */
    protected $isSuccessful;

    /**
     * @var string
     */
    protected $queueStatus;

    /**
     * @var string
     */
    protected $reason;

    /**
     * @var ?string
     */
    protected $failureDescription;

    /**
     * @var string
     */
    protected $sequraLink;

    /**
     * @var string
     */
    protected $shopLink;

    /**
     * @var string[]
     */
    protected $fields = [
        'id',
        'storeId',
        'merchantReference',
        'executionId',
        'paymentMethod',
        'timestamp',
        'eventCode',
        'isSuccessful',
        'queueStatus',
        'reason',
        'failureDescription',
        'sequraLink',
        'shopLink'
    ];

    /**
     * @return EntityConfiguration
     */
    public function getConfig(): EntityConfiguration
    {
        $indexMap = new IndexMap();
        $indexMap->addStringIndex('storeId')
            ->addStringIndex('merchantReference')
            ->addIntegerIndex('executionId')
            ->addIntegerIndex('timestamp');

        return new EntityConfiguration($indexMap, 'TransactionLog');
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
     * @return string
     */
    public function getMerchantReference(): string
    {
        return $this->merchantReference;
    }

    /**
     * @param string $merchantReference
     */
    public function setMerchantReference(string $merchantReference): void
    {
        $this->merchantReference = $merchantReference;
    }

    /**
     * @return int
     */
    public function getExecutionId(): int
    {
        return $this->executionId;
    }

    /**
     * @param int $executionId
     */
    public function setExecutionId(int $executionId): void
    {
        $this->executionId = $executionId;
    }

    /**
     * @return string
     */
    public function getPaymentMethod(): string
    {
        return $this->paymentMethod;
    }

    /**
     * @param string $paymentMethod
     */
    public function setPaymentMethod(string $paymentMethod): void
    {
        $this->paymentMethod = $paymentMethod;
    }

    /**
     * @return int
     */
    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    /**
     * @param int $timestamp
     */
    public function setTimestamp(int $timestamp): void
    {
        $this->timestamp = $timestamp;
    }

    /**
     * @return string
     */
    public function getEventCode(): string
    {
        return $this->eventCode;
    }

    /**
     * @param string $eventCode
     */
    public function setEventCode(string $eventCode): void
    {
        $this->eventCode = $eventCode;
    }

    /**
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return $this->isSuccessful;
    }

    /**
     * @param bool $isSuccessful
     */
    public function setIsSuccessful(bool $isSuccessful): void
    {
        $this->isSuccessful = $isSuccessful;
    }

    /**
     * @return string
     */
    public function getQueueStatus(): string
    {
        return $this->queueStatus;
    }

    /**
     * @param string $queueStatus
     */
    public function setQueueStatus(string $queueStatus): void
    {
        $this->queueStatus = $queueStatus;
    }

    /**
     * @return ?string
     */
    public function getReason(): ?string
    {
        return $this->reason;
    }

    /**
     * @param ?string $reason
     */
    public function setReason(?string $reason): void
    {
        $this->reason = $reason;
    }

    /**
     * @return ?string
     */
    public function getFailureDescription(): ?string
    {
        return $this->failureDescription;
    }

    /**
     * @param ?string $failureDescription
     */
    public function setFailureDescription(?string $failureDescription): void
    {
        $this->failureDescription = $failureDescription;
    }

    /**
     * @return ?string
     */
    public function getSequraLink(): ?string
    {
        return $this->sequraLink;
    }

    /**
     * @param ?string $sequraLink
     */
    public function setSequraLink(?string $sequraLink): void
    {
        $this->sequraLink = $sequraLink;
    }

    /**
     * @return ?string
     */
    public function getShopLink(): ?string
    {
        return $this->shopLink;
    }

    /**
     * @param ?string $shopLink
     */
    public function setShopLink(?string $shopLink): void
    {
        $this->shopLink = $shopLink;
    }
}
