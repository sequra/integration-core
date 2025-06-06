<?php

namespace SeQura\Core\BusinessLogic\Domain\Connection\ProxyContracts;

use SeQura\Core\BusinessLogic\Domain\Connection\Models\Credentials;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\CredentialsRequest;
use SeQura\Core\Infrastructure\Http\Exceptions\HttpRequestException;

/**
 * Class ConnectionProxyInterface
 *
 * @package SeQura\Core\BusinessLogic\Domain\Connection\ProxyContracts
 */
interface ConnectionProxyInterface
{
    /**
     * Tries to call the seQura API in order to validate connection data and fetch credentials data.
     *
     * @param CredentialsRequest $request
     *
     * @return Credentials[]
     *
     * @throws HttpRequestException
     */
    public function getCredentials(CredentialsRequest $request): array;
}
