<?php

namespace SeQura\Core\BusinessLogic\DataAccess\SendReport\Repositories;

use DateTime;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\SendReport\Models\SendReport;
use SeQura\Core\BusinessLogic\Domain\SendReport\RepositoryContracts\SendReportRepositoryInterface;
use SeQura\Core\BusinessLogic\DataAccess\SendReport\Entities\SendReport as SendReportEntity;
use SeQura\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use SeQura\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use SeQura\Core\Infrastructure\ORM\QueryFilter\Operators;
use SeQura\Core\Infrastructure\ORM\QueryFilter\QueryFilter;

/**
 * Class SendReportRepository
 *
 * @package SeQura\Core\BusinessLogic\DataAccess\SendReport\Repositories
 */
class SendReportRepository implements SendReportRepositoryInterface
{
    /**
     * @var RepositoryInterface Statistical data repository.
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
     *
     * @throws QueryFilterInvalidParamException
     */
    public function setSendReport(SendReport $sendReport): void
    {
        $existingSendReport = $this->getSendReportEntity();

        if ($existingSendReport) {
            $existingSendReport->setContext($this->storeContext->getStoreId());
            $existingSendReport->setSendReportTime($sendReport->getSendReportTime());
            $existingSendReport->setSendReport($sendReport);
            $this->repository->update($existingSendReport);

            return;
        }

        $entity = new SendReportEntity();
        $entity->setContext($this->storeContext->getStoreId());
        $entity->setSendReportTime($sendReport->getSendReportTime());
        $entity->setSendReport($sendReport);
        $this->repository->save($entity);
    }

    /**
     * @return SendReport|null
     *
     * @throws QueryFilterInvalidParamException
     */
    public function getSendReport(): ?SendReport
    {
        $entity = $this->getSendReportEntity();

        return $entity ? $entity->getSendReport() : null;
    }

    /**
     * @param string $context
     *
     * @return void
     *
     * @throws QueryFilterInvalidParamException
     */
    public function deleteSendReportForContext(string $context): void
    {
        $entity = $this->getSendReportEntity($context);

        if (!$entity) {
            return;
        }

        $this->repository->delete($entity);
    }

    /**
     * @return string[]
     *
     * @throws QueryFilterInvalidParamException
     */
    public function getReportSendingContexts(): array
    {
        $now = (new DateTime())->getTimestamp();

        $queryFilter = new QueryFilter();
        $queryFilter->where('sendReportTime', Operators::LESS_OR_EQUAL_THAN, $now);

        /**
        * @var SendReportEntity[] $result
        */
        $result = $this->repository->select($queryFilter);

        return $result ? array_map(function ($entity) {
            return $entity->getContext();
        }, $result) : [];
    }

    /**
     * Gets the statistical data entity from the database.
     *
     * @param string|null $context
     *
     * @return SendReportEntity|null
     *
     * @throws QueryFilterInvalidParamException
     */
    protected function getSendReportEntity(?string $context = null): ?SendReportEntity
    {
        $queryFilter = new QueryFilter();
        $queryFilter->where('context', Operators::EQUALS, $context ?? $this->storeContext->getStoreId());

        /**
        * @var SendReportEntity $statisticalData
        */
        $statisticalData = $this->repository->selectOne($queryFilter);

        return $statisticalData;
    }
}
