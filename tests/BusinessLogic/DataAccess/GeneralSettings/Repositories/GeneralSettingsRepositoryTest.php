<?php

namespace SeQura\Core\Tests\BusinessLogic\DataAccess\GeneralSettings\Repositories;

use Exception;
use SeQura\Core\BusinessLogic\DataAccess\GeneralSettings\Entities\GeneralSettings as GeneralSettingsEntity;
use SeQura\Core\BusinessLogic\DataAccess\GeneralSettings\Repositories\GeneralSettingsRepository;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Models\GeneralSettings;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\RepositoryContracts\GeneralSettingsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use SeQura\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\ORM\TestRepositoryRegistry;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class GeneralSettingsRepositoryTest.
 *
 * @package SeQura\Core\Tests\BusinessLogic\DataAccess\GeneralSettings\Repositories
 */
class GeneralSettingsRepositoryTest extends BaseTestCase
{
    /** @var RepositoryInterface */
    private $repository;

    /** @var GeneralSettingsRepositoryInterface */
    private $generalSettingsRepository;

    /**
     * @return void
     *
     * @throws RepositoryClassException
     * @throws RepositoryNotRegisteredException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = TestRepositoryRegistry::getRepository(GeneralSettingsEntity::getClassName());
        $this->generalSettingsRepository = new GeneralSettingsRepository(
            $this->repository,
            StoreContext::getInstance()
        );

        TestServiceRegister::registerService(GeneralSettingsRepositoryInterface::class, function () {
            return $this->generalSettingsRepository;
        });
    }

    /**
     * @throws Exception
     */
    public function testDeleteGeneralSettings(): void
    {
        // arrange
        $generalSettings = new GeneralSettings(
            true,
            null,
            [],
            [],
            []
        );

        $entity = new GeneralSettingsEntity();
        $entity->setStoreId('1');
        $entity->setGeneralSettings($generalSettings);
        $this->repository->save($entity);

        // act
        StoreContext::doWithStore(
            '1',
            [$this->generalSettingsRepository, 'deleteGeneralSettings']
        );

        // assert
        $result = $this->repository->select();
        self::assertCount(0, $result);
    }
}
