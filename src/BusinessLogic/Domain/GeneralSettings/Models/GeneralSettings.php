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
     * Countries enabled for services.
     *
     * @var string[]
     */
    protected $enabledForServices;

    /**
     * Countries where delaying the first payment for services is allowed.
     *
     * @var string[]
     */
    protected $allowFirstServicePaymentDelay;

    /**
     * Countries where charging a registration fee for services is allowed.
     *
     * @var string[]
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
     * @param string[] $enabledForServices
     * @param string[] $allowFirstServicePaymentDelay
     * @param string[] $allowServiceRegistrationItems
     * @param string $defaultServicesEndDate
     */
    public function __construct(
        bool $sendOrderReportsPeriodicallyToSeQura,
        ?bool $showSeQuraCheckoutAsHostedPage,
        ?array $allowedIPAddresses,
        ?array $excludedProducts,
        ?array $excludedCategories,
        array $enabledForServices = [],
        array $allowFirstServicePaymentDelay = [],
        array $allowServiceRegistrationItems = [],
        ?string $defaultServicesEndDate = null
    ) {
        $this->sendOrderReportsPeriodicallyToSeQura = $sendOrderReportsPeriodicallyToSeQura;
        $this->showSeQuraCheckoutAsHostedPage = $showSeQuraCheckoutAsHostedPage;
        $this->allowedIPAddresses = $allowedIPAddresses;
        $this->excludedProducts = $excludedProducts;
        $this->excludedCategories = $excludedCategories;
        $this->setEnabledForServices($enabledForServices);
        $this->setAllowFirstServicePaymentDelay($allowFirstServicePaymentDelay);
        $this->setAllowServiceRegistrationItems($allowServiceRegistrationItems);
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
     * @return string[]
     */
    public function getEnabledForServices(): array
    {
        return $this->enabledForServices;
    }

    /**
     * Normalizes country codes to uppercase and removes duplicates.
     *
     * @param string[] $countryCodes
     *
     * @return string[]
     */
    private function normalizeCountryCodes(array $countryCodes): array
    {
        $normalized = [];
        foreach ($countryCodes as $value) {
            if (is_string($value) && !empty(trim($value))) {
                $normalized[] = strtoupper(trim($value));
            }
        }
        return array_unique($normalized);
    }

    /**
     * @param string[] $enabledForServices
     */
    public function setEnabledForServices(array $enabledForServices): void
    {
        $this->enabledForServices = $this->normalizeCountryCodes($enabledForServices);
    }

    /**
     * @return string[]
     */
    public function getAllowFirstServicePaymentDelay(): array
    {
        return $this->allowFirstServicePaymentDelay;
    }

    /**
     * @param string[] $allowFirstServicePaymentDelay
     */
    public function setAllowFirstServicePaymentDelay(array $allowFirstServicePaymentDelay): void
    {
        $this->allowFirstServicePaymentDelay = $this->normalizeCountryCodes($allowFirstServicePaymentDelay);
    }

    /**
     * @return string[]
     */
    public function getAllowServiceRegistrationItems(): array
    {
        return $this->allowServiceRegistrationItems;
    }

    /**
     * @param string[] $allowServiceRegistrationItems
     */
    public function setAllowServiceRegistrationItems(array $allowServiceRegistrationItems): void
    {
        $this->allowServiceRegistrationItems = $this->normalizeCountryCodes($allowServiceRegistrationItems);
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
