<?php

namespace SeQura\Core\BusinessLogic\Domain\OrderReport\Service;

use SeQura\Core\BusinessLogic\Domain\Integration\OrderReport\OrderReportServiceInterface;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidUrlException;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Merchant;
use SeQura\Core\BusinessLogic\Domain\OrderReport\Models\ReportData;
use SeQura\Core\BusinessLogic\Domain\OrderReport\Models\SendOrderReportRequest;
use SeQura\Core\BusinessLogic\Domain\OrderReport\ProxyContracts\OrderReportProxyInterface;
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
     * @param OrderReportProxyInterface $proxy
     * @param OrderReportServiceInterface $integrationOrderReportService
     */
    public function __construct(
        OrderReportProxyInterface   $proxy,
        OrderReportServiceInterface $integrationOrderReportService
    )
    {
        $this->proxy = $proxy;
        $this->integrationOrderReportService = $integrationOrderReportService;
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
     * Creates a SendOrderReportRequest instance.
     *
     * @param string $merchantId
     * @param array $orderReports
     * @param array|null $orderStatistics
     *
     * @return SendOrderReportRequest
     *
     * @throws InvalidUrlException
     */
    private function createSendOrderReportRequest(
        string $merchantId,
        array  $orderReports,
        array  $orderStatistics = null
    ): SendOrderReportRequest
    {
        return new SendOrderReportRequest(
            new Merchant($merchantId),
            $orderReports,
            $this->integrationOrderReportService->getPlatform(),
            $orderStatistics
        );
    }
}