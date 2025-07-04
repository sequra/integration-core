<?php

namespace SeQura\Core\Tests\BusinessLogic\DataAccess\OrderSettings\Repositories;

use Exception;
use SeQura\Core\BusinessLogic\DataAccess\OrderSettings\Entities\OrderStatusSettings;
use SeQura\Core\BusinessLogic\DataAccess\OrderSettings\Repositories\OrderStatusMappingRepository;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\Models\OrderStatusMapping;
use SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\RepositoryContracts\OrderStatusSettingsRepositoryInterface;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use SeQura\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\ORM\TestRepositoryRegistry;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class OrderSettingsRepositoryTest.
 *
 * @package SeQura\Core\Tests\BusinessLogic\DataAccess\OrderSettings\Repositories
 */
class OrderSettingsRepositoryTest extends BaseTestCase
{
    /** @var RepositoryInterface */
    private $repository;

    /** @var OrderStatusSettingsRepositoryInterface */
    private $orderSettingsRepository;

    /**
     * @return void
     *
     * @throws RepositoryClassException
     * @throws RepositoryNotRegisteredException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = TestRepositoryRegistry::getRepository(OrderStatusSettings::getClassName());
        $this->orderSettingsRepository = new OrderStatusMappingRepository(
            $this->repository,
            StoreContext::getInstance()
        );

        TestServiceRegister::registerService(OrderStatusSettingsRepositoryInterface::class, function () {
            return $this->orderSettingsRepository;
        });
    }

    /**
     * @throws Exception
     */
    public function testDeleteOrderSettings(): void
    {
        // arrange
        $statuses = [
            new OrderStatusMapping('solicited', '1'),
            new OrderStatusMapping('solicited', '2'),
            new OrderStatusMapping('solicited', '3')
        ];

        $entity = new OrderStatusSettings();
        $entity->setStoreId('1');
        $entity->setOrderStatusMappings($statuses);
        $this->repository->save($entity);

        // act
        StoreContext::doWithStore(
            '1',
            [$this->orderSettingsRepository, 'deleteOrderStatusMapping']
        );

        // assert
        $result = $this->repository->select();
        self::assertCount(0, $result);
    }
}
