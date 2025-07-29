<?php

namespace SeQura\Core\Tests\BusinessLogic\DataAccess\Repositories;

use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidEnvironmentException;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\AuthorizationCredentials;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use SeQura\Core\BusinessLogic\Domain\Connection\RepositoryContracts\ConnectionDataRepositoryInterface;
use SeQura\Core\BusinessLogic\SeQuraAPI\BaseProxy;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class ConnectionDataRepositoryTest
 *
 * @package SeQura\Core\Tests\BusinessLogic\DataAccess\Repositories
 */
class ConnectionDataRepositoryTest extends BaseTestCase
{
    /**
     * @var ConnectionDataRepositoryInterface
     */
    private $repository;

    /**
     * @throws RepositoryClassException
     * @throws InvalidEnvironmentException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = TestServiceRegister::getService(ConnectionDataRepositoryInterface::class);

        $connectionData = new ConnectionData(
            BaseProxy::TEST_MODE,
            'test',
            'sequra',
            new AuthorizationCredentials('test_username', 'test_password')
        );

        $this->repository->setConnectionData($connectionData);
    }

    /**
     * @return void
     */
    public function testGetConnectionData(): void
    {
        $connectionData = $this->repository->getConnectionDataByDeploymentId('sequra');

        $this->assertInstanceOf(ConnectionData::class, $connectionData);
        $this->assertEquals(BaseProxy::TEST_MODE, $connectionData->getEnvironment());
        $this->assertEquals('test', $connectionData->getMerchantId());
        $this->assertEquals('test_username', $connectionData->getAuthorizationCredentials()->getUsername());
        $this->assertEquals('test_password', $connectionData->getAuthorizationCredentials()->getPassword());
    }

    /**
     * @throws InvalidEnvironmentException
     */
    public function testSetConnectionDataForExistingEntity(): void
    {
        $connectionData = new ConnectionData(
            BaseProxy::LIVE_MODE,
            'live',
            'sequra',
            new AuthorizationCredentials('live_username', 'live_password')
        );

        $this->repository->setConnectionData($connectionData);
        $connectionData = $this->repository->getConnectionDataByDeploymentId('sequra');

        $this->assertEquals(BaseProxy::LIVE_MODE, $connectionData->getEnvironment());
        $this->assertEquals('live', $connectionData->getMerchantId());
        $this->assertEquals('live_username', $connectionData->getAuthorizationCredentials()->getUsername());
        $this->assertEquals('live_password', $connectionData->getAuthorizationCredentials()->getPassword());
        $this->assertEquals('sequra', $connectionData->getDeployment());
    }
}
