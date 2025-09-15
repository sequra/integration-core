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
     * ISO 8601 duration string representing the default end date for services (1 year).
     */
    const DEFAULT_SERVICE_END_DATE = 'P1Y';

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
     * Whether the integration supports selling services.
     *
     * @var bool
     */
    protected $enabledForServices;

    /**
     * Whether the integration allows delaying the first payment for services.
     *
     * @var bool
     */
    protected $allowFirstServicePaymentDelay;

    /**
     * Whether the integration allows charging a registration fee for services.
     *
     * @var bool
     */
    protected $allowServiceRegistrationItems;

    /**
     * ISO 8601 date or duration string representing the default end date for services.
     *
     * @var string
     */
    protected $defaultServicesEndDate;

    /**
     * @param bool $sendOrderReportsPeriodicallyToSeQura
     * @param bool|null $showSeQuraCheckoutAsHostedPage
     * @param string[]|null $allowedIPAddresses
     * @param string[]|null $excludedProducts
     * @param string[]|null $excludedCategories
     * @param bool $enabledForServices
     * @param bool $allowFirstServicePaymentDelay
     * @param bool $allowServiceRegistrationItems
     * @param string $defaultServicesEndDate
     */
    public function __construct(
        bool $sendOrderReportsPeriodicallyToSeQura,
        ?bool $showSeQuraCheckoutAsHostedPage,
        ?array $allowedIPAddresses,
        ?array $excludedProducts,
        ?array $excludedCategories,
        bool $enabledForServices = false,
        bool $allowFirstServicePaymentDelay = false,
        bool $allowServiceRegistrationItems = false,
        ?string $defaultServicesEndDate = null
    ) {
        $this->sendOrderReportsPeriodicallyToSeQura = $sendOrderReportsPeriodicallyToSeQura;
        $this->showSeQuraCheckoutAsHostedPage = $showSeQuraCheckoutAsHostedPage;
        $this->allowedIPAddresses = $allowedIPAddresses;
        $this->excludedProducts = $excludedProducts;
        $this->excludedCategories = $excludedCategories;
        $this->enabledForServices = $enabledForServices;
        $this->allowFirstServicePaymentDelay = $allowFirstServicePaymentDelay;
        $this->allowServiceRegistrationItems = $allowServiceRegistrationItems;
        $this->setDefaultServicesEndDate($defaultServicesEndDate);
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

    /**
     * @return bool
     */
    public function isEnabledForServices(): bool
    {
        return $this->enabledForServices;
    }

    /**
     * @param bool $enabledForServices
     */
    public function setEnabledForServices(bool $enabledForServices): void
    {
        $this->enabledForServices = $enabledForServices;
    }

    /**
     * @return bool
     */
    public function isAllowFirstServicePaymentDelay(): bool
    {
        return $this->allowFirstServicePaymentDelay;
    }

    /**
     * @param bool $allowFirstServicePaymentDelay
     */
    public function setAllowFirstServicePaymentDelay(bool $allowFirstServicePaymentDelay): void
    {
        $this->allowFirstServicePaymentDelay = $allowFirstServicePaymentDelay;
    }

    /**
     * @return bool
     */
    public function isAllowServiceRegistrationItems(): bool
    {
        return $this->allowServiceRegistrationItems;
    }

    /**
     * @param bool $allowServiceRegistrationItems
     */
    public function setAllowServiceRegistrationItems(bool $allowServiceRegistrationItems): void
    {
        $this->allowServiceRegistrationItems = $allowServiceRegistrationItems;
    }

    /**
     * @return string
     */
    public function getDefaultServicesEndDate(): string
    {
        return $this->defaultServicesEndDate;
    }

    /**
     * @param string|null $defaultServicesEndDate
     */
    public function setDefaultServicesEndDate(?string $defaultServicesEndDate): void
    {
        $this->defaultServicesEndDate = empty($defaultServicesEndDate) ? self::DEFAULT_SERVICE_END_DATE : $defaultServicesEndDate;
    }
}
