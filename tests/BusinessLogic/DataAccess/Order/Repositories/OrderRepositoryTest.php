<?php

namespace SeQura\Core\Tests\BusinessLogic\DataAccess\Order\Repositories;

use Exception;
use SeQura\Core\BusinessLogic\DataAccess\Order\Repositories\SeQuraOrderRepository;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\Order\Models\SeQuraOrder;
use SeQura\Core\BusinessLogic\Domain\Order\RepositoryContracts\SeQuraOrderRepositoryInterface;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use SeQura\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\ORM\TestRepositoryRegistry;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class OrderRepositoryTest.
 *
 * @package SeQura\Core\Tests\BusinessLogic\DataAccess\Order\Repositories
 */
class OrderRepositoryTest extends BaseTestCase
{
    /** @var RepositoryInterface */
    private $repository;

    /** @var SeQuraOrderRepositoryInterface */
    private $seQuraOrderRepository;

    /**
     * @return void
     *
     * @throws RepositoryClassException
     * @throws RepositoryNotRegisteredException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = TestRepositoryRegistry::getRepository(SeQuraOrder::getClassName());
        $this->seQuraOrderRepository = new SeQuraOrderRepository(
            $this->repository
        );

        TestServiceRegister::registerService(SeQuraOrderRepositoryInterface::class, function () {
            return $this->seQuraOrderRepository;
        });
    }

    /**
     * @throws Exception
     */
    public function testDeleteOrders(): void
    {
        // arrange
        $entity = new SeQuraOrder();
        $entity->setCartId('1');
        $entity->setReference('1');
        $this->repository->save($entity);

        // act
        StoreContext::doWithStore(
            '1',
            [$this->seQuraOrderRepository, 'deleteAllOrders']
        );

        // assert
        $result = $this->repository->select();
        self::assertCount(0, $result);
    }
}
