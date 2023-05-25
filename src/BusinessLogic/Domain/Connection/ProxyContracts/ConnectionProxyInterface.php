<?php

namespace SeQura\Core\BusinessLogic\Domain\Connection\ProxyContracts;

use SeQura\Core\BusinessLogic\Domain\Connection\Models\ValidateConnectionRequest;
use SeQura\Core\Infrastructure\Http\Exceptions\HttpRequestException;

/**
 * Class ConnectionProxyInterface
 *
 * @package SeQura\Core\BusinessLogic\Domain\Connection\ProxyContracts
 */
interface ConnectionProxyInterface
{
    /**
     * Tries to call the seQura API in order to validate connection data.
     *
     * @param ValidateConnectionRequest $request
     *
     * @return void
     *
     * @throws HttpRequestException
     */
    public function validateConnection(ValidateConnectionRequest $request): void;
}
