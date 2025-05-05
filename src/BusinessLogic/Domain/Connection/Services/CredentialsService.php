<?php

namespace SeQura\Core\BusinessLogic\Domain\Connection\Services;

use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\BadMerchantIdException;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\WrongCredentialsException;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\Credentials;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\CredentialsRequest;
use SeQura\Core\BusinessLogic\Domain\Connection\ProxyContracts\ConnectionProxyInterface;
use SeQura\Core\BusinessLogic\Domain\Connection\RepositoryContracts\CredentialsRepositoryInterface;
use SeQura\Core\BusinessLogic\SeQuraAPI\Exceptions\HttpApiInvalidUrlParameterException;
use SeQura\Core\BusinessLogic\SeQuraAPI\Exceptions\HttpApiUnauthorizedException;
use SeQura\Core\Infrastructure\Http\Exceptions\HttpRequestException;

/**
 * Class CredentialsService.
 *
 * @package SeQura\Core\BusinessLogic\Domain\Connection\Services
 */
class CredentialsService
{
    /**
     * @var ConnectionProxyInterface $connectionProxy
     */
    protected $connectionProxy;
    /**
     * @var CredentialsRepositoryInterface $credentialsRepository
     */
    protected $credentialsRepository;

    /**
     * @param ConnectionProxyInterface $connectionProxy
     * @param CredentialsRepositoryInterface $credentialsRepository
     */
    public function __construct(
        ConnectionProxyInterface $connectionProxy,
        CredentialsRepositoryInterface $credentialsRepository
    ) {
        $this->connectionProxy = $connectionProxy;
        $this->credentialsRepository = $credentialsRepository;
    }

    /**
     * Attempts to send an HTTP request to the SeQura API with the provided connection data.
     * If response is successful, credentials entities are saved to database.
     *
     * @param ConnectionData $connectionData
     *
     * @return Credentials[]
     *
     * @throws HttpRequestException
     * @throws WrongCredentialsException
     * @throws BadMerchantIdException
     */
    public function validateAndUpdateCredentials(ConnectionData $connectionData): array
    {
        try {
            $credentials = $this->connectionProxy->getCredentials(new CredentialsRequest($connectionData));
        } catch (HttpApiUnauthorizedException $exception) {
            throw new WrongCredentialsException();
        } catch (HttpApiInvalidUrlParameterException $exception) {
            throw new BadMerchantIdException();
        }

        $this->credentialsRepository->deleteCredentials();
        $this->credentialsRepository->setCredentials($credentials);

        return $credentials;
    }

    /**
     * @return Credentials[]
     */
    public function getCredentials(): array
    {
        return $this->credentialsRepository->getCredentials();
    }
}
