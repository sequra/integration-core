<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use Exception;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidEnvironmentException;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\AuthorizationCredentials;
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
     * @var ?ConnectionData
     */
    private $connectionData = null;

    /**
     * @var ConnectionData[] $allConnectionData
     */
    private $allConnectionData = [];

    /**
     * @param array $connections
     *
     * @return void
     *
     * @throws Exception
     */
    public function connect(array $connections): void
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

    /**
     * @param string $deployment
     *
     * @return ?ConnectionData
     */
    public function getConnectionDataByDeployment(string $deployment): ?ConnectionData
    {
        return $this->connectionData;
    }

    /**
     * @param ConnectionData $connectionData
     *
     * @return void
     */
    public function saveConnectionData(ConnectionData $connectionData): void
    {
        $this->connectionData = $connectionData;
    }

    /**
     * @param string $merchantId
     *
     * @return ConnectionData
     *
     * @throws InvalidEnvironmentException
     */
    public function getConnectionDataByMerchantId(string $merchantId): ConnectionData
    {
        if (!$this->connectionData) {
            return new ConnectionData(
                'sandbox',
                'test',
                'sequra',
                new AuthorizationCredentials('test', 'test')
            );
        }

        return $this->connectionData;
    }

    /**
     * @return ConnectionData[]
     */
    public function getAllConnectionData(): array
    {
        if (empty($this->allConnectionData)) {
            return parent::getAllConnectionData();
        }

        return $this->allConnectionData;
    }

    /**
     * @param ConnectionData[] $allConnectionData
     *
     * @return void
     */
    public function setMockAllConnectionData(array $allConnectionData): void
    {
        $this->allConnectionData = $allConnectionData;
    }
}
