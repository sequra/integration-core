<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use Exception;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\Credentials;
use SeQura\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;

/**
 * Class MockConnectionService.
 *
 * @package SeQura\Core\Tests\BusinessLogic\Common\MockComponents
 */
class MockConnectionService extends ConnectionService
{
    /**
     * @var bool $throwError
     */
    private $throwError = false;

    /**
     * @var Credentials[]
     */
    private static $credentials = [];

    /**
     * @param ConnectionData $connectionData
     *
     * @return void
     *
     * @throws Exception
     */
    public function connect(ConnectionData $connectionData): void
    {
        if ($this->throwError) {
            throw new Exception('testing error');
        }
    }

    /**
     * @param bool $throwError
     *
     * @return void
     */
    public function setThrowError(bool $throwError): void
    {
        $this->throwError = $throwError;
    }

    /**
     * @param Credentials[] $credentials
     *
     * @return void
     */
    public function setMockCredentials(array $credentials): void
    {
        self::$credentials = $credentials;
    }

    /**
     * @return Credentials[]
     */
    public function getCredentials(): array
    {
        return self::$credentials;
    }
}
