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
    private $showSeQuraCheckoutAsHostedPage;

    /**
     * @var bool
     */
    private $sendOrderReportsPeriodicallyToSeQura;

    /**
     * @var string[]|null
     */
    private $allowedIPAddresses;

    /**
     * @var Category[]|null
     */
    private $excludedCategories;

    /**
     * @var string[]|null
     */
    private $excludedProducts;

    /**
     * @param bool $showSeQuraCheckoutAsHostedPage
     * @param bool $sendOrderReportsPeriodicallyToSeQura
     * @param string[]|null $allowedIPAddresses
     * @param string[]|null $excludedProducts
     * @param Category[]|null $excludedCategories
     */
    public function __construct(
        bool $showSeQuraCheckoutAsHostedPage,
        bool $sendOrderReportsPeriodicallyToSeQura,
        ?array $allowedIPAddresses,
        ?array $excludedProducts,
        ?array $excludedCategories
    )
    {
        $this->showSeQuraCheckoutAsHostedPage = $showSeQuraCheckoutAsHostedPage;
        $this->sendOrderReportsPeriodicallyToSeQura = $sendOrderReportsPeriodicallyToSeQura;
        $this->allowedIPAddresses = $allowedIPAddresses;
        $this->excludedProducts = $excludedProducts;
        $this->excludedCategories = $excludedCategories;
    }

    /**
     * @return bool
     */
    public function isShowSeQuraCheckoutAsHostedPage(): bool
    {
        return $this->showSeQuraCheckoutAsHostedPage;
    }

    /**
     * @param bool $showSeQuraCheckoutAsHostedPage
     */
    public function setShowSeQuraCheckoutAsHostedPage(bool $showSeQuraCheckoutAsHostedPage): void
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
     * @return Category[]|null
     */
    public function getExcludedCategories(): ?array
    {
        return $this->excludedCategories;
    }

    /**
     * @param Category[]|null $excludedCategories
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
