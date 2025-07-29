<?php

namespace SeQura\Core\BusinessLogic\Domain\Connection\Models;

use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidEnvironmentException;
use SeQura\Core\Infrastructure\Data\DataTransferObject;

/**
 * Class CredentialsRequest
 *
 * @package SeQura\Core\BusinessLogic\Domain\Connection\Models
 */
class CredentialsRequest extends DataTransferObject
{
    /**
     * @var ConnectionData
     */
    protected $connectionData;

    /**
     * @param ConnectionData $connectionData
     */
    public function __construct(ConnectionData $connectionData)
    {
        $this->connectionData = $connectionData;
    }

    /**
     * @return ConnectionData
     */
    public function getConnectionData(): ConnectionData
    {
        return $this->connectionData;
    }

    /**
     * @param ConnectionData $connectionData
     */
    public function setConnectionData(ConnectionData $connectionData): void
    {
        $this->connectionData = $connectionData;
    }

    /**
     * Create a GetAvailablePaymentMethodsRequest instance from an array.
     *
     * @param mixed[] $data
     *
     * @return CredentialsRequest
     *
     * @throws InvalidEnvironmentException
     */
    public static function fromArray(array $data): self
    {
        return new self(
            new ConnectionData(
                self::getDataValue($data, 'environment'),
                self::getDataValue($data, 'merchant_id'),
                self::getDataValue($data, 'deployment'),
                new AuthorizationCredentials(
                    self::getDataValue($data, 'username'),
                    self::getDataValue($data, 'password')
                )
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return $this->connectionData->toArray();
    }
}
