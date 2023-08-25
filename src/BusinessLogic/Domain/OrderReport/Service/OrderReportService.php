<?php

namespace SeQura\Core\BusinessLogic\Domain\OrderReport\Service;

use SeQura\Core\BusinessLogic\Domain\Integration\OrderReport\OrderReportServiceInterface;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidUrlException;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Merchant;
use SeQura\Core\BusinessLogic\Domain\OrderReport\Models\ReportData;
use SeQura\Core\BusinessLogic\Domain\OrderReport\Models\SendOrderReportRequest;
use SeQura\Core\BusinessLogic\Domain\OrderReport\ProxyContracts\OrderReportProxyInterface;
use SeQura\Core\BusinessLogic\Domain\StatisticalData\RepositoryContracts\StatisticalDataRepositoryInterface;
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
    private $proxy;

    /**
     * @var OrderReportServiceInterface
     */
    private $integrationOrderReportService;

    /**
     * @var StatisticalDataRepositoryInterface
     */
    private $statisticalDataRepository;

    /**
     * @param OrderReportProxyInterface $proxy
     * @param OrderReportServiceInterface $integrationOrderReportService
     * @param StatisticalDataRepositoryInterface $statisticalDataRepository
     */
    public function __construct(
        OrderReportProxyInterface          $proxy,
        OrderReportServiceInterface        $integrationOrderReportService,
        StatisticalDataRepositoryInterface $statisticalDataRepository
    )
    {
        $this->proxy = $proxy;
        $this->integrationOrderReportService = $integrationOrderReportService;
        $this->statisticalDataRepository = $statisticalDataRepository;
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
        $orderReports = $this->integrationOrderReportService->getOrderReports();
        $orderStatistics = null;
        $statisticalData = $this->statisticalDataRepository->getStatisticalData();

        if ($statisticalData && $statisticalData->isSendStatisticalData()) {
            $orderStatistics = $this->integrationOrderReportService->getOrderStatistics();
        }

        $reportRequest = $this->createSendOrderReportRequest($reportData, $orderReports, $orderStatistics);

        return $this->proxy->sendReport($reportRequest);
    }

    /**
     * Creates a SendOrderReportRequest instance.
     *
     * @param ReportData $reportData
     * @param array $orderReports
     * @param array|null $orderStatistics
     * @return SendOrderReportRequest
     *
     * @throws InvalidUrlException
     */
    private function createSendOrderReportRequest(
        ReportData $reportData,
        array      $orderReports,
        array      $orderStatistics = null
    ): SendOrderReportRequest
    {
        return new SendOrderReportRequest(
            new Merchant($reportData->getMerchantId()),
            $orderReports,
            $reportData->getPlatform(),
            $orderStatistics
        );
    }
}
