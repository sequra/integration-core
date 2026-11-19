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
    protected $merchantId;

    /**
     * @var string[]
     */
    protected $reportOrderIds;

    /**
     * @var string[]|null
     */
    protected $statisticsOrderIds;

    /**
     * @param string $merchantId
     * @param string[] $reportOrderIds
     * @param string[]|null $statisticsOrderIds
     */
    public function __construct(string $merchantId, array $reportOrderIds, ?array $statisticsOrderIds = null)
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
     * @return string[]|null
     */
    public function getStatisticsOrderIds(): ?array
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
     * @param string[]|null $statisticsOrderIds
     *
     * @return void
     */
    public function setStatisticsOrderIds(?array $statisticsOrderIds): void
    {
        $this->statisticsOrderIds = $statisticsOrderIds;
    }
}
