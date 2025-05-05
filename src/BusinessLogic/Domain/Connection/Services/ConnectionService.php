<?php

namespace SeQura\Core\BusinessLogic\Domain\Connection\Services;

use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\BadMerchantIdException;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\WrongCredentialsException;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\Credentials;
use SeQura\Core\BusinessLogic\Domain\Connection\RepositoryContracts\ConnectionDataRepositoryInterface;
use SeQura\Core\Infrastructure\Http\Exceptions\HttpRequestException;

/**
 * Class ConnectionService
 *
 * @package SeQura\Core\BusinessLogic\Domain\Connection\Services
 */
class ConnectionService
{
    /**
     * @var ConnectionDataRepositoryInterface $connectionDataRepository
     */
    protected $connectionDataRepository;

    /**
     * @var CredentialsService $credentialsService
     */
    protected $credentialsService;

    /**
     * @param ConnectionDataRepositoryInterface $connectionDataRepository
     * @param CredentialsService $credentialsService
     */
    public function __construct(
        ConnectionDataRepositoryInterface $connectionDataRepository,
        CredentialsService $credentialsService
    ) {
        $this->connectionDataRepository = $connectionDataRepository;
        $this->credentialsService = $credentialsService;
    }

    /**
     * @param ConnectionData $connectionData
     *
     * @return void
     *
     * @throws BadMerchantIdException
     * @throws HttpRequestException
     * @throws WrongCredentialsException
     */
    public function connect(ConnectionData $connectionData): void
    {
        $this->credentialsService->validateAndUpdateCredentials($connectionData);
        $this->saveConnectionData($connectionData);
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
        $this->credentialsService->validateAndUpdateCredentials($connectionData);

        return true;
    }

    /**
     * Retrieves connection data from the database via connection data repository.
     *
     * @return ConnectionData|null
     */
    public function getConnectionData(): ?ConnectionData
    {
        return $this->connectionDataRepository->getConnectionData();
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
        $this->connectionDataRepository->setConnectionData($connectionData);
    }

    /**
     * @return Credentials[]
     *
     * @throws BadMerchantIdException
     * @throws HttpRequestException
     * @throws WrongCredentialsException
     */
    public function getCredentials(): array
    {
        $credentials = $this->credentialsService->getCredentials();

        if (!empty($credentials)) {
            return $credentials;
        }

        $connectionData = $this->getConnectionData();

        return $connectionData ? $this->credentialsService->validateAndUpdateCredentials($connectionData) : [];
    }
}
