<?php

namespace SeQura\Core\BusinessLogic\Domain\OrderReport\Models;

/**
 * Class ReportData
 *
 * @package SeQura\Core\BusinessLogic\Domain\OrderReport\Models
 */
class ReportData
{
    /**
     * @var string
     */
    private $merchantId;

    /**
     * @var string[]
     */
    private $reportOrderIds;

    /**
     * @var string[]
     */
    private $statisticsOrderIds;

    /**
     * @param string $merchantId
     * @param string[] $reportOrderIds
     * @param string[] $statisticsOrderIds
     */
    public function __construct(string $merchantId, array $reportOrderIds, array $statisticsOrderIds)
    {
        $this->merchantId = $merchantId;
        $this->reportOrderIds = $reportOrderIds;
        $this->statisticsOrderIds = $statisticsOrderIds;
    }

    /**
     * @return string
     */
    public function getMerchantId(): string
    {
        return $this->merchantId;
    }

    /**
     * @return string[]
     */
    public function getReportOrderIds(): array
    {
        return $this->reportOrderIds;
    }

    /**
     * @return string[]
     */
    public function getStatisticsOrderIds(): array
    {
        return $this->statisticsOrderIds;
    }

    /**
     * @param string $merchantId
     *
     * @return void
     */
    public function setMerchantId(string $merchantId): void
    {
        $this->merchantId = $merchantId;
    }

    /**
     * @param string[] $reportOrderIds
     *
     * @return void
     */
    public function setReportOrderIds(array $reportOrderIds): void
    {
        $this->reportOrderIds = $reportOrderIds;
    }

    /**
     * @param string[] $statisticsOrderIds
     *
     * @return void
     */
    public function setStatisticsOrderIds(array $statisticsOrderIds): void
    {
        $this->statisticsOrderIds = $statisticsOrderIds;
    }
}
