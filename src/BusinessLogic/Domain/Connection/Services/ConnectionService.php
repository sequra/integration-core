<?php

namespace SeQura\Core\BusinessLogic\Domain\Connection\Services;

use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\BadMerchantIdException;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionDataNotFoundException;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\CredentialsNotFoundException;
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
     * @param ConnectionData[] $connections
     *
     * @return void
     *
     * @throws BadMerchantIdException
     * @throws HttpRequestException
     * @throws WrongCredentialsException
     */
    public function connect(array $connections): void
    {
        $errors = [];

        foreach ($connections as $connectionData) {
            try{
                $this->credentialsService->validateAndUpdateCredentials($connectionData);
                $this->saveConnectionData($connectionData);
            } catch (WrongCredentialsException $exception) {
                $errors[] = $connectionData->getDeployment();
            }
        }

        if (!empty($errors)) {
            throw new WrongCredentialsException(null, $errors);
        }
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
     * @return ConnectionData[]
     */
    public function getAllConnectionData(): array
    {
        return $this->connectionDataRepository->getAllConnectionSettings();
    }

    /**
     * Retrieves connection data from the database via connection data repository.
     *
     * @param string $deployment
     *
     * @return ConnectionData|null
     */
    public function getConnectionDataByDeployment(string $deployment): ?ConnectionData
    {
        return $this->connectionDataRepository->getConnectionDataByDeploymentId($deployment);
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

        $connections = $this->getAllConnectionData();
        $credentials = [];
        foreach ($connections as $connectionData) {
            $credentials = $this->credentialsService->validateAndUpdateCredentials($connectionData);
        }

        return $credentials;
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
     * Fetches connection data based on merchant ID. Firstly, credentials are fetched, and then based on deployment,
     * Connection data are fetched.
     *
     * @param string $merchantId
     *
     * @return ConnectionData
     *
     * @throws ConnectionDataNotFoundException
     * @throws CredentialsNotFoundException
     */
    public function getConnectionDataByMerchantId(string $merchantId): ConnectionData
    {
        $credentials = $this->credentialsService->getCredentialsByMerchantId($merchantId);

        if (!$credentials) {
            throw new CredentialsNotFoundException();
        }

        $connectionData = $this->connectionDataRepository->getConnectionDataByDeploymentId($credentials->getDeployment());

        if (!$connectionData) {
            throw new ConnectionDataNotFoundException();
        }

        return $connectionData;
    }
}
