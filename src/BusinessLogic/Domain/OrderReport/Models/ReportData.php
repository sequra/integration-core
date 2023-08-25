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
     * @param string $merchantId
     * @param Platform $platform
     */
    public function __construct(string $merchantId, Platform $platform)
    {
        $this->merchantId = $merchantId;
        $this->platform = $platform;
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
}
