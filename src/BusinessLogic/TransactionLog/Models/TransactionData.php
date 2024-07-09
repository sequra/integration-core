<?php

namespace SeQura\Core\BusinessLogic\TransactionLog\Models;

use SeQura\Core\Infrastructure\Data\DataTransferObject;

/**
 * Class TransactionData
 *
 * @package SeQura\Core\BusinessLogic\TransactionLog\Models
 */
class TransactionData extends DataTransferObject
{
    /**
     * @var string|int
     */
    protected $merchantReference;
    /**
     * @var string
     */
    protected $eventCode;
    /**
     * @var int
     */
    protected $timestamp;
    /**
     * @var string
     */
    protected $reason;
    /**
     * @var bool
     */
    protected $isSuccessful;

    /**
     * @param $merchantReference
     * @param $eventCode
     * @param $timestamp
     * @param $reason
     * @param $isSuccessful
     */
    public function __construct($merchantReference, $eventCode, $timestamp, $reason, $isSuccessful)
    {
        $this->merchantReference = $merchantReference;
        $this->eventCode = $eventCode;
        $this->timestamp = $timestamp;
        $this->reason = $reason;
        $this->isSuccessful = $isSuccessful;
    }

    /**
     * @return int|string
     */
    public function getMerchantReference()
    {
        return $this->merchantReference;
    }

    /**
     * @param int|string $merchantReference
     */
    public function setMerchantReference($merchantReference): void
    {
        $this->merchantReference = $merchantReference;
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
    public function getReason(): string
    {
        return $this->reason;
    }

    /**
     * @param string $reason
     */
    public function setReason(string $reason): void
    {
        $this->reason = $reason;
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
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            'merchantReference' => $this->getMerchantReference(),
            'eventCode' => $this->getEventCode(),
            'timestamp' => $this->getTimestamp(),
            'reason' => $this->getReason(),
            'isSuccessful' => $this->isSuccessful()
        ];
    }

    /**
     * @inheritDoc
     */
    public static function fromArray(array $data): self
    {
        return new self(
            self::getDataValue($data, 'merchantReference'),
            self::getDataValue($data, 'eventCode'),
            self::getDataValue($data, 'timestamp'),
            self::getDataValue($data, 'reason'),
            self::getDataValue($data, 'isSuccessful')
        );
    }
}
