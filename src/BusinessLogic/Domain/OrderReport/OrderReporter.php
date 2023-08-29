<?php

namespace SeQura\Core\BusinessLogic\Domain\OrderReport;

use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\RepositoryContracts\CountryConfigurationRepositoryInterface;
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

    protected $page = 0;

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $result = parent::toArray();
        $result['page'] = $this->page;

        return $result;
    }

    /**
     * @inheritDoc
     */
    public static function fromArray(array $array)
    {
        $entity = parent::fromArray($array);
        $entity->page = $array['page'];

        return $entity;
    }

    /**
     * @inheritDoc
     */
    public function serialize(): ?string
    {
        return Serializer::serialize([
            'parent' => parent::serialize(),
            'page' => $this->page
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
    }

    /**
     * @inheritDoc
     *
     * @throws QueueStorageUnavailableException
     */
    protected function getSubTask(): ?ExecutionDetails
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

        if ((empty($reportOrderIds) && empty($statisticsOrderIds)) || $merchantId) {
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
    private function getShopOrderService(): ShopOrderService
    {
        return ServiceRegister::getService(ShopOrderService::class);
    }

    /**
     * Returns an instance of the statistical data repository.
     *
     * @return StatisticalDataRepositoryInterface
     */
    private function getStatisticalDataRepository(): StatisticalDataRepositoryInterface
    {
        return ServiceRegister::getService(StatisticalDataRepositoryInterface::class);
    }

    /**
     * Returns an instance of the statistical data repository.
     *
     * @return CountryConfigurationRepositoryInterface
     */
    private function getCountryConfigurationRepository(): CountryConfigurationRepositoryInterface
    {
        return ServiceRegister::getService(CountryConfigurationRepositoryInterface::class);
    }
}
