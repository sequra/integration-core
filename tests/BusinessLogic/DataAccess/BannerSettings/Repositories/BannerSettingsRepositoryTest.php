<?php

namespace SeQura\Core\Tests\BusinessLogic\DataAccess\BannerSettings\Repositories;

use Exception;
use SeQura\Core\BusinessLogic\DataAccess\BannerSettings\Entities\BannerSettings as BannerSettingEntity;
use SeQura\Core\BusinessLogic\DataAccess\BannerSettings\Repositories\BannerSettingsRepository;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Models\Banner;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Models\BannerSettings;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\RepositoryContracts\BannerSettingsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use SeQura\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\ORM\TestRepositoryRegistry;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class BannerSettingsRepositoryTest.
 *
 * @package SeQura\Core\Tests\BusinessLogic\DataAccess\BannerSettings\Repositories
 */
class BannerSettingsRepositoryTest extends BaseTestCase
{
    /** @var RepositoryInterface */
    private $repository;

    /** @var BannerSettingsRepositoryInterface */
    private $bannerSettingsRepository;

    /**
     * @return void
     *
     * @throws RepositoryClassException
     * @throws RepositoryNotRegisteredException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = TestRepositoryRegistry::getRepository(BannerSettingEntity::getClassName());
        $this->bannerSettingsRepository = new BannerSettingsRepository(
            $this->repository,
            StoreContext::getInstance()
        );

        TestServiceRegister::registerService(BannerSettingsRepositoryInterface::class, function () {
            return $this->bannerSettingsRepository;
        });
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testGetBannerSettings(): void
    {
        // arrange
        $bannerSettings = new BannerSettings(
            [
                new Banner(
                    'ES',
                    'displayOnHomePage',
                    'https://www.sequra.com/es/faq#shoppers',
                    'https://shop/sequra/es/image.jpg'
                ),
                new Banner(
                    'PT',
                    'displayOnCartPage',
                    'https://www.sequra.com/it/faq#shoppers',
                    'https://shop/sequra/pt/image.jpg'
                )
            ]
        );

        $entity = new BannerSettingEntity();
        $entity->setStoreId('1');
        $entity->setBannerSettings($bannerSettings);
        $this->repository->save($entity);

        // act
        StoreContext::doWithStore(
            '1',
            [$this->bannerSettingsRepository, 'getBannerSettings']
        );

        // assert
        /** @var BannerSettingEntity[] $result */
        $result = $this->repository->select();

        self::assertCount(1, $result);
        self::assertEquals($bannerSettings, $result[0]->getBannerSettings());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testSetBannerSettings(): void
    {
        // arrange
        $bannerSettings = new BannerSettings(
            [
                new Banner(
                    'ES',
                    'displayOnHomePage',
                    'https://www.sequra.com/es/faq#shoppers',
                    ''
                ),
                new Banner(
                    'PT',
                    'displayOnCartPage',
                    '',
                    'https://shop/sequra/pt/image.jpg'
                )
            ]
        );

        // act
        StoreContext::doWithStore(
            '1',
            [$this->bannerSettingsRepository, 'setBannerSettings'],
            [$bannerSettings]
        );

        // assert
        /** @var BannerSettingEntity[] $result */
        $result = $this->repository->select();

        self::assertCount(1, $result);
        self::assertEquals($result[0]->getBannerSettings(), $bannerSettings);
    }

    /**
     * @throws Exception
     */
    public function testDeleteBannerSettings(): void
    {
        // arrange
        $bannerSettings = new BannerSettings(
            [
                new Banner(
                    'ES',
                    'displayOnHomePage',
                    'https://www.sequra.com/es/faq#shoppers',
                    'https://shop/sequra/es/image.jpg'
                ),
                new Banner(
                    'PT',
                    'displayOnCartPage',
                    'https://www.sequra.com/it/faq#shoppers',
                    'https://shop/sequra/pt/image.jpg'
                )
            ]
        );

        $entity = new BannerSettingEntity();
        $entity->setStoreId('1');
        $entity->setBannerSettings($bannerSettings);
        $this->repository->save($entity);

        // act
        StoreContext::doWithStore(
            '1',
            [$this->bannerSettingsRepository, 'deleteBannerSettings']
        );

        // assert
        $result = $this->repository->select();
        self::assertCount(0, $result);
    }
}
