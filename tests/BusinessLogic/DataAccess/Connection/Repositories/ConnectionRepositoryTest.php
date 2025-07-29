<?php

namespace SeQura\Core\Tests\BusinessLogic\DataAccess\Connection\Repositories;

use Exception;
use SeQura\Core\BusinessLogic\DataAccess\ConnectionData\Entities\ConnectionData as ConnectionDataEntity;
use SeQura\Core\BusinessLogic\DataAccess\ConnectionData\Repositories\ConnectionDataRepository;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\AuthorizationCredentials;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use SeQura\Core\BusinessLogic\Domain\Connection\RepositoryContracts\ConnectionDataRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use SeQura\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\ORM\TestRepositoryRegistry;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class ConnectionRepositoryTest.
 *
 * @package SeQura\Core\Tests\BusinessLogic\DataAccess\Connection\Repositories
 */
class ConnectionRepositoryTest extends BaseTestCase
{
    /** @var RepositoryInterface */
    private $repository;

    /** @var ConnectionDataRepositoryInterface */
    private $connectionDataRepository;

    /**
     * @return void
     *
     * @throws RepositoryClassException
     * @throws RepositoryNotRegisteredException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = TestRepositoryRegistry::getRepository(ConnectionDataEntity::getClassName());
        $this->connectionDataRepository = new ConnectionDataRepository(
            $this->repository,
            StoreContext::getInstance()
        );

        TestServiceRegister::registerService(ConnectionDataRepository::class, function () {
            return $this->connectionDataRepository;
        });
    }

    /**
     * @throws Exception
     */
    public function testDeleteConnectionDataByDeploymentId(): void
    {
        // arrange
        $connectionData1 = new ConnectionData(
            'sandbox',
            'merchant',
            'sequra',
            new AuthorizationCredentials('username', 'password')
        );
        $entity = new ConnectionDataEntity();
        $entity->setDeployment('sequra');
        $entity->setStoreId('1');
        $entity->setConnectionData($connectionData1);
        $this->repository->save($entity);

        $connectionData2 = new ConnectionData(
            'sandbox',
            'merchant',
            'svea',
            new AuthorizationCredentials('username', 'password')
        );
        $entity = new ConnectionDataEntity();
        $entity->setDeployment('sequra');
        $entity->setStoreId('1');
        $entity->setConnectionData($connectionData2);
        $this->repository->save($entity);

        // act
        StoreContext::doWithStore(
            '1',
            [$this->connectionDataRepository, 'deleteConnectionDataByDeploymentId'],
            ['sequra',]
        );

        // assert
        $result = $this->repository->select();
        self::assertCount(1, $result);
        self::assertEquals($connectionData2, $result[0]->getConnectionData());
    }
}
