<?php

namespace SeQura\Core\BusinessLogic\Domain\OrderReport;

use Exception;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\RepositoryContracts\CountryConfigurationRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\OrderReport\Tasks\OrderReportTask;
use SeQura\Core\BusinessLogic\Domain\StatisticalData\RepositoryContracts\StatisticalDataRepositoryInterface;
use SeQura\Core\BusinessLogic\Webhook\Services\ShopOrderService;
use SeQura\Core\Infrastructure\Serializer\Serializer;
use SeQura\Core\Infrastructure\ServiceRegister;
use SeQura\Core\Infrastructure\TaskExecution\Composite\ExecutionDetails;
use SeQura\Core\Infrastructure\TaskExecution\Composite\Orchestrator;
use SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException;

/**
 * Class OrderReporter
 *
 * @package SeQura\Core\BusinessLogic\Domain\OrderReport
 */
class OrderReporter extends Orchestrator
{
    protected const ORDERS_PER_BACH = 5000;
    protected $page = 1;

    protected $storeId;

    public function __construct()
    {
        $this->storeId = StoreContext::getInstance()->getStoreId();
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $result = parent::toArray();
        $result['page'] = $this->page;
        $result['storeId'] = $this->storeId;

        return $result;
    }

    /**
     * @inheritDoc
     */
    public static function fromArray(array $array)
    {
        $entity = parent::fromArray($array);
        $entity->page = $array['page'];
        $entity->storeId = $array['storeId'];

        return $entity;
    }

    /**
     * @inheritDoc
     */
    public function serialize(): ?string
    {
        return Serializer::serialize([
            'parent' => parent::serialize(),
            'page' => $this->page,
            'storeId' => $this->storeId,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function unserialize($serialized): void
    {
        $unserialized = Serializer::unserialize($serialized);
        parent::unserialize($unserialized['parent']);
        $this->page = $unserialized['page'];
        $this->storeId = $unserialized['storeId'];
    }

    /**
     * @inheritDoc
     */
    public function __unserialize($data): void
    {
        parent::__unserialize($data);
        $this->page = $data['page'];
    }

    /**
     * @inheritDoc
     *
     * @throws Exception
     */
    protected function getSubTask(): ?ExecutionDetails
    {
        return StoreContext::doWithStore($this->storeId, function () {
            return $this->getSubTaskInContext();
        });
    }

    /**
     * @return ExecutionDetails|null
     *
     * @throws QueueStorageUnavailableException
     */
    protected function getSubTaskInContext(): ?ExecutionDetails
    {
        $reportOrderIds = $this->getShopOrderService()->getReportOrderIds($this->page, static::ORDERS_PER_BACH);

        $statisticsOrderIds = null;
        $statisticalData = $this->getStatisticalDataRepository()->getStatisticalData();
        if ($statisticalData && $statisticalData->isSendStatisticalData()) {
            $statisticsOrderIds = $this->getShopOrderService()->getStatisticsOrderIds($this->page, static::ORDERS_PER_BACH);
        }

        $merchantId = null;
        $countryConfiguration = $this->getCountryConfigurationRepository()->getCountryConfiguration();
        if ($countryConfiguration && $countryConfiguration[0]) {
            $merchantId = $countryConfiguration[0]->getMerchantId();
        }

        if ((empty($reportOrderIds) && empty($statisticsOrderIds)) || !$merchantId) {
            return null;
        }

        $this->page++;

        return $this->createSubJob(new OrderReportTask($merchantId, $reportOrderIds, $statisticsOrderIds));
    }

    /**
     * Provides product service.
     *
     * @return ShopOrderService
     */
    protected function getShopOrderService(): ShopOrderService
    {
        return ServiceRegister::getService(ShopOrderService::class);
    }

    /**
     * Returns an instance of the statistical data repository.
     *
     * @return StatisticalDataRepositoryInterface
     */
    protected function getStatisticalDataRepository(): StatisticalDataRepositoryInterface
    {
        return ServiceRegister::getService(StatisticalDataRepositoryInterface::class);
    }

    /**
     * Returns an instance of the statistical data repository.
     *
     * @return CountryConfigurationRepositoryInterface
     */
    protected function getCountryConfigurationRepository(): CountryConfigurationRepositoryInterface
    {
        return ServiceRegister::getService(CountryConfigurationRepositoryInterface::class);
    }
}
