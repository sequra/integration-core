<?php

namespace SeQura\Core\Tests\BusinessLogic\DataAccess\TransactionLog\Repositories;

use Exception;
use SeQura\Core\BusinessLogic\DataAccess\TransactionLog\Entities\TransactionLog as TransactionLogEntity;
use SeQura\Core\BusinessLogic\DataAccess\TransactionLog\Repositories\TransactionLogRepository;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\TransactionLog\RepositoryContracts\TransactionLogRepositoryInterface;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use SeQura\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\ORM\TestRepositoryRegistry;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class TransactionLogRepository.
 *
 * @package SeQura\Core\Tests\BusinessLogic\DataAccess\TransactionLog\Repositories
 */
class TransactionLogRepositoryTest extends BaseTestCase
{
    /** @var RepositoryInterface */
    private $repository;

    /** @var TransactionLogRepositoryInterface */
    private $transactionLogRepository;

    /**
     * @return void
     *
     * @throws RepositoryClassException
     * @throws RepositoryNotRegisteredException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = TestRepositoryRegistry::getRepository(TransactionLogEntity::getClassName());
        $this->transactionLogRepository = new TransactionLogRepository(
            $this->repository,
            StoreContext::getInstance()
        );

        TestServiceRegister::registerService(TransactionLogRepositoryInterface::class, function () {
            return $this->transactionLogRepository;
        });
    }

    /**
     * @throws Exception
     */
    public function testDeleteStatisticalData(): void
    {
        // arrange
        $entity = new TransactionLogEntity();
        $entity->setStoreId('1');
        $entity->setMerchantReference('1');
        $entity->setExecutionId(1);
        $entity->setTimestamp(1);
        $this->repository->save($entity);

        // act
        StoreContext::doWithStore(
            '1',
            [$this->transactionLogRepository, 'deleteAllTransactionLogs']
        );

        // assert
        $result = $this->repository->select();
        self::assertCount(0, $result);
    }
}
