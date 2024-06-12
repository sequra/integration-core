<?php

namespace SeQura\Core\BusinessLogic\SeQuraAPI\Authorization;

use SeQura\Core\BusinessLogic\Domain\Connection\RepositoryContracts\ConnectionDataRepositoryInterface;
use SeQura\Core\BusinessLogic\SeQuraAPI\BaseProxy;
use SeQura\Core\Infrastructure\Http\HttpClient;

/**
 * Class AuthorizedProxy
 *
 * @package SeQura\Core\BusinessLogic\SeQuraAPI\Authorization
 */
class AuthorizedProxy extends BaseProxy
{
    public const AUTHORIZATION_HEADER_KEY = 'Authorization';
    public const AUTHORIZATION_HEADER_VALUE_PREFIX = 'Authorization: Basic ';

    /**
     * @var ConnectionDataRepositoryInterface
     */
    protected $connectionDataRepository;

    /**
     * AuthorizedProxy constructor.
     *
     * @param HttpClient $client
     * @param ConnectionDataRepositoryInterface $connectionDataRepository
     */
    public function __construct(
        HttpClient $client,
        ConnectionDataRepositoryInterface $connectionDataRepository
    ) {
        $connectionData = $connectionDataRepository->getConnectionData();
        parent::__construct($client, $connectionData ? $connectionData->getEnvironment() : self::TEST_MODE);

        $this->connectionDataRepository = $connectionDataRepository;
    }

    /**
     * Retrieves request headers.
     *
     * @return array Complete list of request headers.
     */
    protected function getHeaders(): array
    {
        $connectionData = $this->connectionDataRepository->getConnectionData();

        if (!$connectionData) {
            return parent::getHeaders();
        }

        $token = base64_encode(sprintf(
            '%s:%s',
            $connectionData->getAuthorizationCredentials()->getUsername(),
            $connectionData->getAuthorizationCredentials()->getPassword()
        ));

        return array_merge(
            parent::getHeaders(),
            [self::AUTHORIZATION_HEADER_KEY => self::AUTHORIZATION_HEADER_VALUE_PREFIX . $token]
        );
    }
}
