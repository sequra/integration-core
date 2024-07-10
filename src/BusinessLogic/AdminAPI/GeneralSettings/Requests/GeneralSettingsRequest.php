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
     * @param bool $enabledForServices Default value is false
     * @param bool $allowFirstServicePaymentDelay Default value is false
     * @param bool $allowServiceRegItems Default value is false
     * @param string $defaultServicesEndDate Default value is 'P1Y'
     */
    public function __construct(
        bool $sendOrderReportsPeriodicallyToSeQura,
        ?bool $showSeQuraCheckoutAsHostedPage,
        ?array $allowedIPAddresses,
        ?array $excludedProducts,
        ?array $excludedCategories,
        bool $enabledForServices = false,
        bool $allowFirstServicePaymentDelay = false,
        bool $allowServiceRegItems = false,
        string $defaultServicesEndDate = 'P1Y'
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
            $this->allowServiceRegItems,
            $this->defaultServicesEndDate
        );
    }
}
