<?php

namespace SeQura\Core\Tests\BusinessLogic\DataAccess\StoreIntegration\Repositories;

use Exception;
use SeQura\Core\BusinessLogic\DataAccess\StoreIntegration\Entities\StoreIntegration as StoreIntegrationEntity;
use SeQura\Core\BusinessLogic\DataAccess\StoreIntegration\Repositories\StoreIntegrationRepository;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models\StoreIntegration;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\RepositoryContracts\StoreIntegrationRepositoryInterface;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use SeQura\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\ORM\TestRepositoryRegistry;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class StoreIntegrationRepositoryTest.
 *
 * @package DataAccess\StoreIntegration\Repositories
 */
class StoreIntegrationRepositoryTest extends BaseTestCase
{
    /** @var RepositoryInterface */
    private $repository;

    /** @var StoreIntegrationRepositoryInterface */
    private $storeIntegrationRepository;

    /**
     * @return void
     *
     * @throws RepositoryClassException
     * @throws RepositoryNotRegisteredException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = TestRepositoryRegistry::getRepository(StoreIntegrationEntity::getClassName());
        $this->storeIntegrationRepository = new StoreIntegrationRepository(
            TestRepositoryRegistry::getRepository(StoreIntegrationEntity::getClassName()),
            StoreContext::getInstance()
        );

        TestServiceRegister::registerService(StoreIntegrationRepositoryInterface::class, function () {
            return $this->storeIntegrationRepository;
        });
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testGetStoreIntegrationNoStoreIntegration(): void
    {
        // act
        $result = StoreContext::doWithStore(
            '1',
            [$this->storeIntegrationRepository, 'getStoreIntegration']
        );

        // assert
        self::assertEmpty($result);
    }

    /**
     * @throws Exception
     */
    public function testGetStoreIntegration(): void
    {
        // arrange
        $storeIntegration = new StoreIntegration('1', 'signature', 'integrationId', 'webhookUrl');
        $entity = new StoreIntegrationEntity();

        $entity->setStoreIntegration($storeIntegration);
        $entity->setStoreId('1');
        $this->repository->save($entity);

        // act
        $result = StoreContext::doWithStore(
            '1',
            [$this->storeIntegrationRepository, 'getStoreIntegration']
        );

        // assert
        self::assertEquals($storeIntegration, $result);
    }

    /**
     * @throws Exception
     */
    public function testGetStoreIntegrationDifferentStores(): void
    {
        // arrange
        $storeIntegration1 = new StoreIntegration('1', 'signature', 'integrationId', 'webhookUrl');
        $entity = new StoreIntegrationEntity();
        $entity->setStoreIntegration($storeIntegration1);
        $entity->setStoreId('1');
        $this->repository->save($entity);

        $storeIntegration2 = new StoreIntegration('2', 'signature2', 'integrationId2', 'webhookUrl2');
        $entity = new StoreIntegrationEntity();
        $entity->setStoreIntegration($storeIntegration2);
        $entity->setStoreId('2');
        $this->repository->save($entity);

        // act
        $result1 = StoreContext::doWithStore(
            '1',
            [$this->storeIntegrationRepository, 'getStoreIntegration']
        );
        $result2 = StoreContext::doWithStore(
            '2',
            [$this->storeIntegrationRepository, 'getStoreIntegration']
        );

        // assert
        self::assertEquals($storeIntegration1, $result1);
        self::assertEquals($storeIntegration2, $result2);
    }

    /**
     * @throws Exception
     */
    public function testSetStoreIntegration(): void
    {
        // arrange
        $storeIntegration = new StoreIntegration('1', 'signature', 'integrationId', 'webhookUrl');

        // act
        StoreContext::doWithStore(
            '1',
            [$this->storeIntegrationRepository, 'setStoreIntegration'],
            [$storeIntegration]
        );

        // assert
        $savedEntity = $this->repository->select();
        self::assertEquals($storeIntegration, $savedEntity[0]->getStoreIntegration());;
    }

    /**
     * @throws Exception
     */
    public function testUpdateStoreIntegration(): void
    {
        // arrange
        $storeIntegration1 = new StoreIntegration('1', 'signature', 'integrationId', 'webhookUrl');
        $storeIntegration2 = new StoreIntegration('1', 'signature2', 'integrationId2', 'webhookUrl2');

        // act
        StoreContext::doWithStore(
            '1',
            [$this->storeIntegrationRepository, 'setStoreIntegration'],
            [$storeIntegration1]
        );
        StoreContext::doWithStore(
            '1',
            [$this->storeIntegrationRepository, 'setStoreIntegration'],
            [$storeIntegration2]
        );

        // assert
        $savedEntity = $this->repository->select();
        self::assertEquals($storeIntegration2, $savedEntity[0]->getStoreIntegration());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testDeleteStoreIntegration(): void
    {
        // arrange
        $storeIntegration = new StoreIntegration('1', 'signature', 'integrationId', 'webhookUrl');
        $entity = new StoreIntegrationEntity();
        $entity->setStoreIntegration($storeIntegration);
        $entity->setStoreId('1');
        $this->repository->save($entity);

        // act
        StoreContext::doWithStore(
            '1',
            [$this->storeIntegrationRepository, 'deleteStoreIntegration']
        );

        // assert
        $entities = $this->repository->select();
        self::assertCount(0, $entities);
    }
}
