<?php

namespace SeQura\Core\BusinessLogic\Domain\Connection\Services;

use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\BadMerchantIdException;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\WrongCredentialsException;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\ValidateConnectionRequest;
use SeQura\Core\BusinessLogic\Domain\Connection\ProxyContracts\ConnectionProxyInterface;
use SeQura\Core\BusinessLogic\Domain\Connection\RepositoryContracts\ConnectionDataRepositoryInterface;
use SeQura\Core\BusinessLogic\SeQuraAPI\Exceptions\HttpApiInvalidUrlParameterException;
use SeQura\Core\BusinessLogic\SeQuraAPI\Exceptions\HttpApiUnauthorizedException;
use SeQura\Core\Infrastructure\Http\Exceptions\HttpRequestException;
use SeQura\Core\Infrastructure\Http\HttpClient;
use SeQura\Core\Infrastructure\ServiceRegister;

/**
 * Class ConnectionService
 *
 * @package SeQura\Core\BusinessLogic\Domain\Connection\Services
 */
class ConnectionService
{
    /**
     * @var ConnectionProxyInterface
     */
    protected $connectionProxy;

    /**
     * @param ConnectionProxyInterface $connectionProxy
     */
    public function __construct(ConnectionProxyInterface $connectionProxy)
    {
        $this->connectionProxy = $connectionProxy;
    }

    /**
     * Attempts to send an HTTP request to the SeQura API with the provided connection data.
     *
     * @param ConnectionData $connectionData
     *
     * @return bool
     *
     * @throws HttpRequestException
     * @throws WrongCredentialsException
     * @throws BadMerchantIdException
     */
    public function isConnectionDataValid(ConnectionData $connectionData): bool
    {
        try {
            $this->connectionProxy->validateConnection(new ValidateConnectionRequest($connectionData));
        } catch (HttpApiUnauthorizedException $exception) {
            throw new WrongCredentialsException();
        } catch (HttpApiInvalidUrlParameterException $exception) {
            throw new BadMerchantIdException();
        }

        return true;
    }

    /**
     * Retrieves connection data from the database via connection data repository.
     *
     * @return ConnectionData|null
     */
    public function getConnectionData(): ?ConnectionData
    {
        return $this->getConnectionDataRepository()->getConnectionData();
    }

    /**
     * Calls the repository to save the connection data to the database.
     *
     * @param ConnectionData $connectionData
     *
     * @return void
     */
    public function saveConnectionData(ConnectionData $connectionData): void
    {
        $this->getConnectionDataRepository()->setConnectionData($connectionData);
    }

    /**
     * Returns an instance of the connection data repository.
     *
     * @return ConnectionDataRepositoryInterface
     */
    protected function getConnectionDataRepository(): ConnectionDataRepositoryInterface
    {
        return ServiceRegister::getService(ConnectionDataRepositoryInterface::class);
    }
}
