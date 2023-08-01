<?php

namespace SeQura\Core\BusinessLogic\DataAccess\PromotionalWidgets\Repositories;

use SeQura\Core\BusinessLogic\DataAccess\PromotionalWidgets\Entities\WidgetLabels as WidgetLabelsEntity;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\WidgetLabels;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\RepositoryContracts\WidgetLabelsRepositoryInterface;
use SeQura\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use SeQura\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use SeQura\Core\Infrastructure\ORM\QueryFilter\Operators;
use SeQura\Core\Infrastructure\ORM\QueryFilter\QueryFilter;

/**
 * Class WidgetLabelsRepository
 *
 * @package SeQura\Core\BusinessLogic\DataAccess\PromotionalWidgets\Repositories
 */
class WidgetLabelsRepository implements WidgetLabelsRepositoryInterface
{
    /**
     * @var RepositoryInterface Widget labels repository.
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
    public function setWidgetLabels(WidgetLabels $labels): void
    {
        $existingWidgetLabels = $this->getWidgetLabelsEntity();

        if ($existingWidgetLabels) {
            $existingWidgetLabels->setWidgetLabels($labels);
            $existingWidgetLabels->setStoreId($this->storeContext->getStoreId());
            $this->repository->update($existingWidgetLabels);

            return;
        }

        $entity = new WidgetLabelsEntity();
        $entity->setStoreId($this->storeContext->getStoreId());
        $entity->setWidgetLabels($labels);
        $this->repository->save($entity);
    }

    /**
     * @inheritDoc
     */
    public function getWidgetLabels(): ?WidgetLabels
    {
        $entity = $this->getWidgetLabelsEntity();

        return $entity ? $entity->getWidgetLabels() : null;
    }

    /**
     * @return WidgetLabelsEntity|null
     *
     * @throws QueryFilterInvalidParamException
     */
    protected function getWidgetLabelsEntity(): ?WidgetLabelsEntity
    {
        $queryFilter = new QueryFilter();
        $queryFilter->where('storeId', Operators::EQUALS, $this->storeContext->getStoreId());

        /** @var  WidgetLabelsEntity $widgetConfiguration */
        $widgetConfiguration = $this->repository->selectOne($queryFilter);

        return $widgetConfiguration;
    }
}