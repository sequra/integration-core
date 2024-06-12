<?php

namespace SeQura\Core\BusinessLogic\DataAccess\PromotionalWidgets\Repositories;

use SeQura\Core\BusinessLogic\DataAccess\PromotionalWidgets\Entities\WidgetSettings as WidgetSettingsEntity;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\WidgetSettings;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\RepositoryContracts\WidgetSettingsRepositoryInterface;
use SeQura\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use SeQura\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use SeQura\Core\Infrastructure\ORM\QueryFilter\Operators;
use SeQura\Core\Infrastructure\ORM\QueryFilter\QueryFilter;

/**
 * Class WidgetSettingsRepository
 *
 * @package SeQura\Core\BusinessLogic\DataAccess\PromotionalWidgets\Repositories
 */
class WidgetSettingsRepository implements WidgetSettingsRepositoryInterface
{
    /**
     * @var RepositoryInterface Widget settings repository.
     */
    protected $repository;

    /**
     * @var StoreContext Store context needed for multistore environment.
     */
    protected $storeContext;

    /**
     * @param RepositoryInterface $repository
     * @param StoreContext $storeContext
     */
    public function __construct(RepositoryInterface $repository, StoreContext $storeContext)
    {
        $this->repository = $repository;
        $this->storeContext = $storeContext;
    }

    /**
     * @inheritDoc
     */
    public function setWidgetSettings(WidgetSettings $settings): void
    {
        $existingWidgetSettings = $this->getWidgetSettingsEntity();

        if ($existingWidgetSettings) {
            $existingWidgetSettings->setWidgetSettings($settings);
            $existingWidgetSettings->setStoreId($this->storeContext->getStoreId());
            $this->repository->update($existingWidgetSettings);

            return;
        }

        $entity = new WidgetSettingsEntity();
        $entity->setStoreId($this->storeContext->getStoreId());
        $entity->setWidgetSettings($settings);
        $this->repository->save($entity);
    }

    /**
     * @inheritDoc
     */
    public function getWidgetSettings(): ?WidgetSettings
    {
        $entity = $this->getWidgetSettingsEntity();

        return $entity ? $entity->getWidgetSettings() : null;
    }

    /**
     * Gets the widget settings entity from the database.
     *
     * @return WidgetSettingsEntity|null
     *
     * @throws QueryFilterInvalidParamException
     */
    protected function getWidgetSettingsEntity(): ?WidgetSettingsEntity
    {
        $queryFilter = new QueryFilter();
        $queryFilter->where('storeId', Operators::EQUALS, $this->storeContext->getStoreId());

        /**
        * @var WidgetSettingsEntity $widgetSettings
        */
        $widgetSettings = $this->repository->selectOne($queryFilter);

        return $widgetSettings;
    }
}
