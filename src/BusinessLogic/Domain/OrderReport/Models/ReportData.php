<?php

namespace SeQura\Core\BusinessLogic\Domain\OrderReport\Models;

use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Platform;

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
     * @var Platform
     */
    private $platform;

    /**
     * @var bool
     */
    private $sendDeliveryReport;

    /**
     * @param string $merchantId
     * @param Platform $platform
     * @param bool $sendDeliveryReport
     */
    public function __construct(string $merchantId, Platform $platform, bool $sendDeliveryReport = true)
    {
        $this->merchantId = $merchantId;
        $this->platform = $platform;
        $this->sendDeliveryReport = $sendDeliveryReport;
    }

    /**
     * @return string
     */
    public function getMerchantId(): string
    {
        return $this->merchantId;
    }

    /**
     * @return Platform
     */
    public function getPlatform(): Platform
    {
        return $this->platform;
    }

    /**
     * @return bool
     */
    public function isSendDeliveryReport(): bool
    {
        return $this->sendDeliveryReport;
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
     * @param Platform $platform
     *
     * @return void
     */
    public function setPlatform(Platform $platform): void
    {
        $this->platform = $platform;
    }

    /**
     * @param bool $sendDeliveryReport
     *
     * @return void
     */
    public function setSendDeliveryReport(bool $sendDeliveryReport): void
    {
        $this->sendDeliveryReport = $sendDeliveryReport;
    }
}
