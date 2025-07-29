<?php

namespace SeQura\Core\Tests\BusinessLogic\DataAccess\StatisticalData\Repositories;

use Exception;
use SeQura\Core\BusinessLogic\DataAccess\StatisticalData\Entities\StatisticalData as StatisticalDataEntity;
use SeQura\Core\BusinessLogic\DataAccess\StatisticalData\Repositories\StatisticalDataRepository;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\StatisticalData\Models\StatisticalData;
use SeQura\Core\BusinessLogic\Domain\StatisticalData\RepositoryContracts\StatisticalDataRepositoryInterface;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use SeQura\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\ORM\TestRepositoryRegistry;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class StatisticalDataRepositoryTest.
 *
 * @package SeQura\Core\Tests\BusinessLogic\DataAccess\StatisticalData\Repositories
 */
class StatisticalDataRepositoryTest extends BaseTestCase
{
    /** @var RepositoryInterface */
    private $repository;

    /** @var StatisticalDataRepositoryInterface */
    private $statisticalDataRepository;

    /**
     * @return void
     *
     * @throws RepositoryClassException
     * @throws RepositoryNotRegisteredException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = TestRepositoryRegistry::getRepository(StatisticalDataEntity::getClassName());
        $this->statisticalDataRepository = new StatisticalDataRepository(
            $this->repository,
            StoreContext::getInstance()
        );

        TestServiceRegister::registerService(StatisticalDataRepositoryInterface::class, function () {
            return $this->statisticalDataRepository;
        });
    }

    /**
     * @throws Exception
     */
    public function testDeleteStatisticalData(): void
    {
        // arrange
        $statisticalData = new StatisticalData(true);

        $entity = new StatisticalDataEntity();
        $entity->setStoreId('1');
        $entity->setStatisticalData($statisticalData);
        $this->repository->save($entity);

        // act
        StoreContext::doWithStore(
            '1',
            [$this->statisticalDataRepository, 'deleteStatisticalData']
        );

        // assert
        $result = $this->repository->select();
        self::assertCount(0, $result);
    }
}
