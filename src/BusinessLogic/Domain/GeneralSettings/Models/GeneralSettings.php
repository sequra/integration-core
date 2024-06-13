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
    protected $sendOrderReportsPeriodicallyToSeQura;

    /**
     * @var bool|null
     */
    protected $showSeQuraCheckoutAsHostedPage;

    /**
     * @var string[]|null
     */
    protected $allowedIPAddresses;

    /**
     * @var string[]|null
     */
    protected $excludedCategories;

    /**
     * @var string[]|null
     */
    protected $excludedProducts;

    /**
     * @var bool
     */
    private $enabledForServices;

    /**
     * @var bool
     */
    private $allowFirstServicePaymentDelay;

    /**
     * @var bool
     */
    private $allowServiceRegItems;

    /**
     * @var string
     */
    private $defaultServicesEndDate;

    /**
     * @param bool $sendOrderReportsPeriodicallyToSeQura
     * @param bool|null $showSeQuraCheckoutAsHostedPage
     * @param string[]|null $allowedIPAddresses
     * @param string[]|null $excludedProducts
     * @param string[]|null $excludedCategories
     * @param bool $enabledForServices
     * @param bool $allowFirstServicePaymentDelay
     * @param bool $allowServiceRegItems
     * @param string $defaultServicesEndDate
     */
    public function __construct(
        bool $sendOrderReportsPeriodicallyToSeQura,
        ?bool $showSeQuraCheckoutAsHostedPage,
        ?array $allowedIPAddresses,
        ?array $excludedProducts,
        ?array $excludedCategories,
        bool $enabledForServices,
        bool $allowFirstServicePaymentDelay,
        bool $allowServiceRegItems,
        string $defaultServicesEndDate
    ) {
        $this->sendOrderReportsPeriodicallyToSeQura = $sendOrderReportsPeriodicallyToSeQura;
        $this->showSeQuraCheckoutAsHostedPage = $showSeQuraCheckoutAsHostedPage;
        $this->allowedIPAddresses = $allowedIPAddresses;
        $this->excludedProducts = $excludedProducts;
        $this->excludedCategories = $excludedCategories;
        $this->enabledForServices = $enabledForServices;
        $this->allowFirstServicePaymentDelay = $allowFirstServicePaymentDelay;
        $this->allowServiceRegItems = $allowServiceRegItems;
        $this->defaultServicesEndDate = $defaultServicesEndDate;
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

    public function isEnabledForServices(): bool
    {
        return $this->enabledForServices;
    }

    public function setEnabledForServices(bool $enabledForServices): void
    {
        $this->enabledForServices = $enabledForServices;
    }

    public function isAllowFirstServicePaymentDelay(): bool
    {
        return $this->allowFirstServicePaymentDelay;
    }

    public function setAllowFirstServicePaymentDelay(bool $allowFirstServicePaymentDelay): void
    {
        $this->allowFirstServicePaymentDelay = $allowFirstServicePaymentDelay;
    }

    public function isAllowServiceRegItems(): bool
    {
        return $this->allowServiceRegItems;
    }

    public function setAllowServiceRegItems(bool $allowServiceRegItems): void
    {
        $this->allowServiceRegItems = $allowServiceRegItems;
    }

    public function getDefaultServicesEndDate(): string
    {
        return $this->defaultServicesEndDate;
    }

    public function setDefaultServicesEndDate(string $defaultServicesEndDate): void
    {
        $this->defaultServicesEndDate = $defaultServicesEndDate;
    }
}
