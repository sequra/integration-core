<?php

namespace SeQura\Core\Tests\BusinessLogic\DataAccess\AdvancedSettings\Repositories;

use Exception;
use SeQura\Core\BusinessLogic\DataAccess\AdvancedSettings\Entities\AdvancedSettings as AdvancedSettingsEntity;
use SeQura\Core\BusinessLogic\DataAccess\AdvancedSettings\Repositories\AdvancedSettingsRepository;
use SeQura\Core\BusinessLogic\Domain\AdvancedSettings\Models\AdvancedSettings;
use SeQura\Core\BusinessLogic\Domain\AdvancedSettings\RepositoryContracts\AdvancedSettingsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use SeQura\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\ORM\TestRepositoryRegistry;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class AdvancedSettingsRepositoryTest.
 *
 * @package DataAccess\AdvancedSettings\Repositories
 */
class AdvancedSettingsRepositoryTest extends BaseTestCase
{
    /** @var RepositoryInterface */
    private $repository;

    /** @var AdvancedSettingsRepositoryInterface */
    private $advancedSettingsRepository;

    /**
     * @return void
     *
     * @throws RepositoryClassException
     * @throws RepositoryNotRegisteredException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = TestRepositoryRegistry::getRepository(AdvancedSettingsEntity::getClassName());
        $this->advancedSettingsRepository = new AdvancedSettingsRepository(
            TestRepositoryRegistry::getRepository(AdvancedSettingsEntity::getClassName()),
            StoreContext::getInstance()
        );

        TestServiceRegister::registerService(AdvancedSettingsRepositoryInterface::class, function () {
            return $this->advancedSettingsRepository;
        });
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testGetSettingsNoSettings(): void
    {
        // act
        $result = StoreContext::doWithStore(
            '1',
            [$this->advancedSettingsRepository, 'getAdvancedSettings']
        );

        // assert
        self::assertEmpty($result);
    }


    /**
     * @throws Exception
     */
    public function testGetAdvancedSettings(): void
    {
        // arrange
        $advancedSettings = new AdvancedSettings(true, 1);
        $entity = new AdvancedSettingsEntity();
        ;
        $entity->setAdvancedSettings($advancedSettings);
        $entity->setStoreId('1');
        $this->repository->save($entity);

        // act
        $result = StoreContext::doWithStore(
            '1',
            [$this->advancedSettingsRepository, 'getAdvancedSettings']
        );


        // assert
        self::assertEquals($advancedSettings, $result);
    }

    /**
     * @throws Exception
     */
    public function testGetSettingsDifferentStores(): void
    {
        // arrange
        $advancedSettings1 = new AdvancedSettings(true, 1);
        $entity = new AdvancedSettingsEntity();
        ;
        $entity->setAdvancedSettings($advancedSettings1);
        $entity->setStoreId('1');
        $this->repository->save($entity);

        $advancedSettings2 = new AdvancedSettings(false, 2);
        $entity = new AdvancedSettingsEntity();
        ;
        $entity->setAdvancedSettings($advancedSettings2);
        $entity->setStoreId('2');
        $this->repository->save($entity);

        // act
        $result1 = StoreContext::doWithStore(
            '1',
            [$this->advancedSettingsRepository, 'getAdvancedSettings']
        );
        $result2 = StoreContext::doWithStore(
            '2',
            [$this->advancedSettingsRepository, 'getAdvancedSettings']
        );

        // assert
        self::assertEquals($advancedSettings1, $result1);
        self::assertEquals($advancedSettings2, $result2);
    }

    /**
     * @throws Exception
     */
    public function testSetCredentials(): void
    {
        // arrange
        $advancedSettings = new AdvancedSettings(true, 1);

        // act
        StoreContext::doWithStore(
            '1',
            [$this->advancedSettingsRepository, 'setAdvancedSettings'],
            [$advancedSettings]
        );

        // assert
        $savedEntity = $this->repository->select();
        self::assertEquals($advancedSettings, $savedEntity[0]->getAdvancedSettings());
        ;
    }

    /**
     * @throws Exception
     */
    public function testUpdateCredentials(): void
    {
        // arrange
        $advancedSettings1 = new AdvancedSettings(true, 1);
        $advancedSettings2 = new AdvancedSettings(false, 2);

        // act
        StoreContext::doWithStore(
            '1',
            [$this->advancedSettingsRepository, 'setAdvancedSettings'],
            [$advancedSettings1]
        );
        StoreContext::doWithStore(
            '1',
            [$this->advancedSettingsRepository, 'setAdvancedSettings'],
            [$advancedSettings2]
        );

        // assert
        $savedEntity = $this->repository->select();
        self::assertEquals($advancedSettings2, $savedEntity[0]->getAdvancedSettings());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testDeleteCredentials(): void
    {
        // arrange
        $advancedSettings1 = new AdvancedSettings(true, 1);
        $entity = new AdvancedSettingsEntity();
        ;
        $entity->setAdvancedSettings($advancedSettings1);
        $entity->setStoreId('1');
        $this->repository->save($entity);

        // act
        StoreContext::doWithStore(
            '1',
            [$this->advancedSettingsRepository, 'deleteAdvancedSettings'],
        );

        // assert
        $entities = $this->repository->select();
        self::assertCount(0, $entities);
    }
}
