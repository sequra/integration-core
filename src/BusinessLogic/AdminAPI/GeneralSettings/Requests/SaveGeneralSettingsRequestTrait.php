<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\GeneralSettings\Requests;

use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Models\GeneralSettings;

/**
 * Trait SaveGeneralSettingsRequestTrait
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\GeneralSettings\Requests
 */
trait SaveGeneralSettingsRequestTrait
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
     * ISO 8601 date or duration string representing the default end date for services.
     *
     * @var string|null
     */
    protected $defaultServicesEndDate;
    /**
     * @var string[] $enabledForServices
     */
    protected $enabledForServices;
    /**
     * @var string[] $allowFirstServicePaymentDelay
     */
    protected $allowFirstServicePaymentDelay;
    /**
     * @var string[] $allowServiceRegistrationItems
     */
    protected $allowServiceRegistrationItems;
    /**
     * Order identifier sent to SeQura as the primary order reference.
     *
     * @var string|null
     */
    protected $orderIdentifier;

    /**
     * @param bool $sendOrderReportsPeriodicallyToSeQura
     * @param bool|null $showSeQuraCheckoutAsHostedPage
     * @param string[]|null $allowedIPAddresses
     * @param string[]|null $excludedProducts
     * @param string[]|null $excludedCategories
     * @param string|null $defaultServicesEndDate
     * @param string[] $enabledForServices
     * @param string[] $allowFirstServicePaymentDelay
     * @param string[] $allowServiceRegistrationItems
     * @param string|null $orderIdentifier
     */
    public function __construct(
        bool $sendOrderReportsPeriodicallyToSeQura,
        ?bool $showSeQuraCheckoutAsHostedPage,
        ?array $allowedIPAddresses,
        ?array $excludedProducts,
        ?array $excludedCategories,
        ?string $defaultServicesEndDate = null,
        array $enabledForServices = [],
        array $allowFirstServicePaymentDelay = [],
        array $allowServiceRegistrationItems = [],
        ?string $orderIdentifier = null
    ) {
        $this->sendOrderReportsPeriodicallyToSeQura = $sendOrderReportsPeriodicallyToSeQura;
        $this->showSeQuraCheckoutAsHostedPage = $showSeQuraCheckoutAsHostedPage;
        $this->allowedIPAddresses = $allowedIPAddresses;
        $this->excludedProducts = $excludedProducts;
        $this->excludedCategories = $excludedCategories;
        $this->defaultServicesEndDate = $defaultServicesEndDate;
        $this->enabledForServices = $enabledForServices;
        $this->allowFirstServicePaymentDelay = $allowFirstServicePaymentDelay;
        $this->allowServiceRegistrationItems = $allowServiceRegistrationItems;
        $this->orderIdentifier = $orderIdentifier;
    }

    /**
     * Transforms the request to a GeneralSettings object.
     *
     * @return GeneralSettings
     */
    public function transformToDomainModel(): object
    {
        return new GeneralSettings(
            $this->sendOrderReportsPeriodicallyToSeQura,
            $this->showSeQuraCheckoutAsHostedPage,
            $this->allowedIPAddresses,
            $this->excludedProducts,
            $this->excludedCategories,
            $this->enabledForServices,
            $this->allowFirstServicePaymentDelay,
            $this->allowServiceRegistrationItems,
            $this->defaultServicesEndDate,
            $this->orderIdentifier
        );
    }
}
