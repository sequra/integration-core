<?php

namespace SeQura\Core\Tests\BusinessLogic\DataAccess\Affiliate\Repositories;

use Exception;
use SeQura\Core\BusinessLogic\DataAccess\Affiliate\Entities\AffiliateSettings as AffiliateSettingsEntity;
use SeQura\Core\BusinessLogic\DataAccess\Affiliate\Repositories\AffiliateSettingsRepository;
use SeQura\Core\BusinessLogic\Domain\Affiliate\Models\AffiliateSettings;
use SeQura\Core\BusinessLogic\Domain\Affiliate\RepositoryContracts\AffiliateSettingsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use SeQura\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\ORM\TestRepositoryRegistry;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class AffiliateSettingsRepositoryTest.
 *
 * @package DataAccess\Affiliate\Repositories
 */
class AffiliateSettingsRepositoryTest extends BaseTestCase
{
    /** @var RepositoryInterface */
    private $repository;

    /** @var AffiliateSettingsRepositoryInterface */
    private $affiliateSettingsRepository;

    /**
     * @return void
     *
     * @throws RepositoryClassException
     * @throws RepositoryNotRegisteredException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = TestRepositoryRegistry::getRepository(AffiliateSettingsEntity::getClassName());
        $this->affiliateSettingsRepository = new AffiliateSettingsRepository(
            TestRepositoryRegistry::getRepository(AffiliateSettingsEntity::getClassName()),
            StoreContext::getInstance()
        );

        TestServiceRegister::registerService(AffiliateSettingsRepositoryInterface::class, function () {
            return $this->affiliateSettingsRepository;
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
            [$this->affiliateSettingsRepository, 'getAffiliateSettings']
        );

        // assert
        self::assertEmpty($result);
    }

    /**
     * @throws Exception
     */
    public function testGetAffiliateSettings(): void
    {
        // arrange
        $affiliateSettings = new AffiliateSettings(true, '1234', 'abc123token');
        $entity = new AffiliateSettingsEntity();

        $entity->setAffiliateSettings($affiliateSettings);
        $entity->setStoreId('1');
        $this->repository->save($entity);

        // act
        $result = StoreContext::doWithStore(
            '1',
            [$this->affiliateSettingsRepository, 'getAffiliateSettings']
        );

        // assert
        self::assertEquals($affiliateSettings, $result);
    }

    /**
     * @throws Exception
     */
    public function testGetSettingsDifferentStores(): void
    {
        // arrange
        $affiliateSettings1 = new AffiliateSettings(true, '1234', 'tokenone');
        $entity = new AffiliateSettingsEntity();
        $entity->setAffiliateSettings($affiliateSettings1);
        $entity->setStoreId('1');
        $this->repository->save($entity);

        $affiliateSettings2 = new AffiliateSettings(false, '5678', 'tokentwo');
        $entity = new AffiliateSettingsEntity();
        $entity->setAffiliateSettings($affiliateSettings2);
        $entity->setStoreId('2');
        $this->repository->save($entity);

        // act
        $result1 = StoreContext::doWithStore(
            '1',
            [$this->affiliateSettingsRepository, 'getAffiliateSettings']
        );
        $result2 = StoreContext::doWithStore(
            '2',
            [$this->affiliateSettingsRepository, 'getAffiliateSettings']
        );

        // assert
        self::assertEquals($affiliateSettings1, $result1);
        self::assertEquals($affiliateSettings2, $result2);
    }

    /**
     * @throws Exception
     */
    public function testSetAffiliateSettings(): void
    {
        // arrange
        $affiliateSettings = new AffiliateSettings(true, '1234', 'abc123token');

        // act
        StoreContext::doWithStore(
            '1',
            [$this->affiliateSettingsRepository, 'setAffiliateSettings'],
            [$affiliateSettings]
        );

        // assert
        $savedEntity = $this->repository->select();
        self::assertEquals($affiliateSettings, $savedEntity[0]->getAffiliateSettings());
    }

    /**
     * @throws Exception
     */
    public function testUpdateAffiliateSettings(): void
    {
        // arrange
        $affiliateSettings1 = new AffiliateSettings(true, '1234', 'tokenone');
        $affiliateSettings2 = new AffiliateSettings(false, '5678', 'tokentwo');

        // act
        StoreContext::doWithStore(
            '1',
            [$this->affiliateSettingsRepository, 'setAffiliateSettings'],
            [$affiliateSettings1]
        );
        StoreContext::doWithStore(
            '1',
            [$this->affiliateSettingsRepository, 'setAffiliateSettings'],
            [$affiliateSettings2]
        );

        // assert
        $savedEntity = $this->repository->select();
        self::assertCount(1, $savedEntity);
        self::assertEquals($affiliateSettings2, $savedEntity[0]->getAffiliateSettings());
    }
}
