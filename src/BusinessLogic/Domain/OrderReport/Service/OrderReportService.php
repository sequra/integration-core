<?php

namespace SeQura\Core\BusinessLogic\Domain\OrderReport\Service;

use SeQura\Core\BusinessLogic\Domain\Integration\OrderReport\OrderReportServiceInterface;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidUrlException;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Merchant;
use SeQura\Core\BusinessLogic\Domain\OrderReport\Models\OrderReport;
use SeQura\Core\BusinessLogic\Domain\OrderReport\Models\OrderStatistics;
use SeQura\Core\BusinessLogic\Domain\OrderReport\Models\ReportData;
use SeQura\Core\BusinessLogic\Domain\OrderReport\Models\SendOrderReportRequest;
use SeQura\Core\BusinessLogic\Domain\OrderReport\Models\Statistics;
use SeQura\Core\BusinessLogic\Domain\OrderReport\ProxyContracts\OrderReportProxyInterface;
use SeQura\Core\BusinessLogic\Domain\SendReport\Models\SendReport;
use SeQura\Core\BusinessLogic\Domain\SendReport\RepositoryContracts\SendReportRepositoryInterface;
use SeQura\Core\Infrastructure\Http\Exceptions\HttpRequestException;

/**
 * Class OrderReportService
 *
 * @package SeQura\Core\BusinessLogic\Domain\OrderReport\Service
 */
class OrderReportService
{
    /**
     * @var OrderReportProxyInterface
     */
    protected $proxy;

    /**
     * @var OrderReportServiceInterface
     */
    protected $integrationOrderReportService;

    /**
     * @var SendReportRepositoryInterface
     */
    protected $sendReportRepository;

    /**
     * @param OrderReportProxyInterface $proxy
     * @param OrderReportServiceInterface $integrationOrderReportService
     * @param SendReportRepositoryInterface $sendReportRepository
     */
    public function __construct(
        OrderReportProxyInterface $proxy,
        OrderReportServiceInterface $integrationOrderReportService,
        SendReportRepositoryInterface $sendReportRepository
    ) {
        $this->proxy = $proxy;
        $this->integrationOrderReportService = $integrationOrderReportService;
        $this->sendReportRepository = $sendReportRepository;
    }

    /**
     * Sends the order delivery report to SeQura.
     *
     * @param ReportData $reportData
     *
     * @return bool
     *
     * @throws HttpRequestException
     * @throws InvalidUrlException
     */
    public function sendReport(ReportData $reportData): bool
    {
        $orderReports = [];
        if (!empty($reportData->getReportOrderIds())) {
            $orderReports = $this->integrationOrderReportService->getOrderReports($reportData->getReportOrderIds());
        }

        $orderStatistics = null;
        if (!empty($reportData->getStatisticsOrderIds())) {
            $orderStatistics = $this->integrationOrderReportService->getOrderStatistics(
                $reportData->getStatisticsOrderIds()
            );
        }

        $reportRequest = $this->createSendOrderReportRequest(
            $reportData->getMerchantId(),
            $orderReports,
            $orderStatistics
        );

        return $this->proxy->sendReport($reportRequest);
    }

    /**
     * @param int $time
     *
     * @return void
     */
    public function setSendReportTime(int $time): void
    {
        $this->sendReportRepository->setSendReport(new SendReport($time));
    }

    /**
     * Creates a SendOrderReportRequest instance.
     *
     * @param string $merchantId
     * @param OrderReport[] $orderReports
     * @param OrderStatistics[]|null $orderStatistics
     *
     * @return SendOrderReportRequest
     *
     * @throws InvalidUrlException
     */
    protected function createSendOrderReportRequest(
        string $merchantId,
        array $orderReports,
        array $orderStatistics = null
    ): SendOrderReportRequest {
        return new SendOrderReportRequest(
            new Merchant($merchantId),
            $orderReports,
            $this->integrationOrderReportService->getPlatform(),
            $orderStatistics ? new Statistics($orderStatistics) : null
        );
    }
}
