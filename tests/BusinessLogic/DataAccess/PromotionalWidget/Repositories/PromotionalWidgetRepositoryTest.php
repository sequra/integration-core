<?php

namespace SeQura\Core\Tests\BusinessLogic\DataAccess\PromotionalWidget\Repositories;

use Exception;
use SeQura\Core\BusinessLogic\DataAccess\PromotionalWidgets\Entities\WidgetSettings as WidgetSettingsEntity;
use SeQura\Core\BusinessLogic\DataAccess\PromotionalWidgets\Repositories\WidgetSettingsRepository;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\WidgetSettings;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\RepositoryContracts\WidgetSettingsRepositoryInterface;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use SeQura\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\ORM\TestRepositoryRegistry;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class PromotionalWidgetRepositoryTest.
 *
 * @package SeQura\Core\Tests\BusinessLogic\DataAccess\PromotionalWidget\Repositories
 */
class PromotionalWidgetRepositoryTest extends BaseTestCase
{
    /** @var RepositoryInterface */
    private $repository;

    /** @var WidgetSettingsRepositoryInterface */
    private $widgetSettingsRepository;

    /**
     * @return void
     *
     * @throws RepositoryClassException
     * @throws RepositoryNotRegisteredException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = TestRepositoryRegistry::getRepository(WidgetSettingsEntity::getClassName());
        $this->widgetSettingsRepository = new WidgetSettingsRepository(
            $this->repository,
            StoreContext::getInstance()
        );

        TestServiceRegister::registerService(WidgetSettingsRepositoryInterface::class, function () {
            return $this->widgetSettingsRepository;
        });
    }

    /**
     * @throws Exception
     */
    public function testDeleteWidgetSettings(): void
    {
        // arrange
        $widget = new WidgetSettings(true);

        $entity = new WidgetSettingsEntity();
        $entity->setStoreId('1');
        $entity->setWidgetSettings($widget);
        $this->repository->save($entity);

        // act
        StoreContext::doWithStore(
            '1',
            [$this->widgetSettingsRepository, 'deleteWidgetSettings']
        );

        // assert
        $result = $this->repository->select();
        self::assertCount(0, $result);
    }
}
