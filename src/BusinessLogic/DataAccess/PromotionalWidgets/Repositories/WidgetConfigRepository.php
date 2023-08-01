<?php

namespace SeQura\Core\BusinessLogic\DataAccess\PromotionalWidgets\Repositories;

use SeQura\Core\BusinessLogic\DataAccess\PromotionalWidgets\Entities\WidgetConfiguration as WidgetConfigurationEntity;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\WidgetConfiguration;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\RepositoryContracts\WidgetConfigRepositoryInterface;
use SeQura\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use SeQura\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use SeQura\Core\Infrastructure\ORM\QueryFilter\Operators;
use SeQura\Core\Infrastructure\ORM\QueryFilter\QueryFilter;

/**
 * Class WidgetConfigRepository
 *
 * @package SeQura\Core\BusinessLogic\DataAccess\PromotionalWidgets\Repositories
 */
class WidgetConfigRepository implements WidgetConfigRepositoryInterface
{
    /**
     * @var RepositoryInterface Widget configuration repository.
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
    public function setWidgetConfig(WidgetConfiguration $configuration): void
    {
        $existingWidgetConfig = $this->getWidgetConfigurationEntity();

        if ($existingWidgetConfig) {
            $existingWidgetConfig->setWidgetConfig($configuration);
            $existingWidgetConfig->setStoreId($this->storeContext->getStoreId());
            $this->repository->update($existingWidgetConfig);

            return;
        }

        $entity = new WidgetConfigurationEntity();
        $entity->setStoreId($this->storeContext->getStoreId());
        $entity->setWidgetConfig($configuration);
        $this->repository->save($entity);
    }

    /**
     * @inheritDoc
     */
    public function getWidgetConfig(): ?WidgetConfiguration
    {
        $entity = $this->getWidgetConfigurationEntity();

        return $entity ? $entity->getWidgetConfig() : null;
    }

    /**
     * Gets the widget configuration entity from the database.
     *
     * @return WidgetConfigurationEntity|null
     *
     * @throws QueryFilterInvalidParamException
     */
    protected function getWidgetConfigurationEntity(): ?WidgetConfigurationEntity
    {
        $queryFilter = new QueryFilter();
        $queryFilter->where('storeId', Operators::EQUALS, $this->storeContext->getStoreId());

        /** @var  WidgetConfigurationEntity $widgetConfiguration */
        $widgetConfiguration = $this->repository->selectOne($queryFilter);

        return $widgetConfiguration;
    }
}