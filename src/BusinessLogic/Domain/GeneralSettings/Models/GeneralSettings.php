<?php

namespace SeQura\Core\BusinessLogic\Domain\GeneralSettings\Models;

/**
 * Class GeneralSettings
 *
 * @package SeQura\Core\BusinessLogic\Domain\GeneralSettings\Models
 */
class GeneralSettings
{
    /**
     * @var bool
     */
    private $sendOrderReportsPeriodicallyToSeQura;

    /**
     * @var bool|null
     */
    private $showSeQuraCheckoutAsHostedPage;

    /**
     * @var string[]|null
     */
    private $allowedIPAddresses;

    /**
     * @var string[]|null
     */
    private $excludedCategories;

    /**
     * @var string[]|null
     */
    private $excludedProducts;

    /**
     * @param bool $sendOrderReportsPeriodicallyToSeQura
     * @param bool|null $showSeQuraCheckoutAsHostedPage
     * @param string[]|null $allowedIPAddresses
     * @param string[]|null $excludedProducts
     * @param string[]|null $excludedCategories
     */
    public function __construct(
        bool    $sendOrderReportsPeriodicallyToSeQura,
        ?bool   $showSeQuraCheckoutAsHostedPage,
        ?array  $allowedIPAddresses,
        ?array  $excludedProducts,
        ?array  $excludedCategories
    )
    {
        $this->sendOrderReportsPeriodicallyToSeQura = $sendOrderReportsPeriodicallyToSeQura;
        $this->showSeQuraCheckoutAsHostedPage = $showSeQuraCheckoutAsHostedPage;
        $this->allowedIPAddresses = $allowedIPAddresses;
        $this->excludedProducts = $excludedProducts;
        $this->excludedCategories = $excludedCategories;
    }

    /**
     * @return bool|null
     */
    public function isShowSeQuraCheckoutAsHostedPage(): ?bool
    {
        return $this->showSeQuraCheckoutAsHostedPage;
    }

    /**
     * @param bool|null $showSeQuraCheckoutAsHostedPage
     */
    public function setShowSeQuraCheckoutAsHostedPage(?bool $showSeQuraCheckoutAsHostedPage): void
    {
        $this->showSeQuraCheckoutAsHostedPage = $showSeQuraCheckoutAsHostedPage;
    }

    /**
     * @return bool
     */
    public function isSendOrderReportsPeriodicallyToSeQura(): bool
    {
        return $this->sendOrderReportsPeriodicallyToSeQura;
    }

    /**
     * @param bool $sendOrderReportsPeriodicallyToSeQura
     */
    public function setSendOrderReportsPeriodicallyToSeQura(bool $sendOrderReportsPeriodicallyToSeQura): void
    {
        $this->sendOrderReportsPeriodicallyToSeQura = $sendOrderReportsPeriodicallyToSeQura;
    }

    /**
     * @return string[]|null
     */
    public function getAllowedIPAddresses(): ?array
    {
        return $this->allowedIPAddresses;
    }

    /**
     * @param string[]|null $allowedIPAddresses
     */
    public function setAllowedIPAddresses(?array $allowedIPAddresses): void
    {
        $this->allowedIPAddresses = $allowedIPAddresses;
    }

    /**
     * @return string[]|null
     */
    public function getExcludedCategories(): ?array
    {
        return $this->excludedCategories;
    }

    /**
     * @param string[]|null $excludedCategories
     */
    public function setExcludedCategories(?array $excludedCategories): void
    {
        $this->excludedCategories = $excludedCategories;
    }

    /**
     * @return string[]|null
     */
    public function getExcludedProducts(): ?array
    {
        return $this->excludedProducts;
    }

    /**
     * @param string[]|null $excludedProducts
     */
    public function setExcludedProducts(?array $excludedProducts): void
    {
        $this->excludedProducts = $excludedProducts;
    }
}
