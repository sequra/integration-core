<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\GeneralSettings\Requests;

use SeQura\Core\BusinessLogic\AdminAPI\Request\Request;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Models\GeneralSettings;

/**
 * Class GeneralSettingsRequest
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\GeneralSettings\Requests
 */
class GeneralSettingsRequest extends Request
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
     * @var string|null
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
     * @param string|null $defaultServicesEndDate
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
        $this->defaultServicesEndDate = $defaultServicesEndDate;
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
            $this->defaultServicesEndDate
        );
    }
}
