<?php

namespace SeQura\Core\BusinessLogic\Domain\OrderReport;

use Generator;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\RepositoryContracts\CountryConfigurationRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\TaskExecution\BusinessTaskInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\TaskExecution\TaskExecutorInterface;
use SeQura\Core\BusinessLogic\Domain\OrderReport\Models\ReportData;
use SeQura\Core\BusinessLogic\Domain\OrderReport\Service\OrderReportService;
use SeQura\Core\BusinessLogic\Domain\StatisticalData\RepositoryContracts\StatisticalDataRepositoryInterface;
use SeQura\Core\BusinessLogic\Webhook\Services\ShopOrderService;
use SeQura\Core\Infrastructure\ServiceRegister;

/**
 * @package SeQura\Core\BusinessLogic\Domain\OrderReport
 */
class OrderReporter implements BusinessTaskInterface
{
    protected const ORDERS_PER_PAGE = 5000;

    /**
     * @var int
     */
    private $page;

    /**
     * @param int $page
     */
    public function __construct(int $page = 1)
    {
        $this->page = $page;
    }

    /**
     * @inheritDoc
     */
    public function execute(): Generator
    {
        $reportOrderIds = $this->getShopOrderService()->getReportOrderIds($this->page, self::ORDERS_PER_PAGE);

        $statisticsOrderIds = null;
        $statisticalData = $this->getStatisticalDataRepository()->getStatisticalData();
        if ($statisticalData && $statisticalData->isSendStatisticalData()) {
            $statisticsOrderIds = $this->getShopOrderService()->getStatisticsOrderIds(
                $this->page,
                self::ORDERS_PER_PAGE
            );
        }

        $merchantId = $this->getMerchantId();

        if ((!empty($reportOrderIds) || !empty($statisticsOrderIds)) && $merchantId) {
            $this->getOrderReportService()->sendReport(
                new ReportData($merchantId, $reportOrderIds, $statisticsOrderIds)
            );
            $this->getTaskExecutor()->enqueue(new self($this->page + 1));
        }

        yield 100;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return ['page' => $this->page];
    }

    /**
     * @inheritDoc
     */
    public static function fromArray(array $data): BusinessTaskInterface
    {
        return new self(\intval($data['page']));
    }

    /**
     * @return string|null
     */
    private function getMerchantId(): ?string
    {
        $countryConfiguration = $this->getCountryConfigurationRepository()->getCountryConfiguration();

        return $countryConfiguration && $countryConfiguration[0] ? $countryConfiguration[0]->getMerchantId() : null;
    }

    /**
     * @return ShopOrderService
     */
    protected function getShopOrderService(): ShopOrderService
    {
        return ServiceRegister::getService(ShopOrderService::class);
    }

    /**
     * @return StatisticalDataRepositoryInterface
     */
    protected function getStatisticalDataRepository(): StatisticalDataRepositoryInterface
    {
        return ServiceRegister::getService(StatisticalDataRepositoryInterface::class);
    }

    /**
     * @return CountryConfigurationRepositoryInterface
     */
    protected function getCountryConfigurationRepository(): CountryConfigurationRepositoryInterface
    {
        return ServiceRegister::getService(CountryConfigurationRepositoryInterface::class);
    }

    /**
     * @return OrderReportService
     */
    protected function getOrderReportService(): OrderReportService
    {
        return ServiceRegister::getService(OrderReportService::class);
    }

    /**
     * @return TaskExecutorInterface
     */
    protected function getTaskExecutor(): TaskExecutorInterface
    {
        return ServiceRegister::getService(TaskExecutorInterface::class);
    }
}
