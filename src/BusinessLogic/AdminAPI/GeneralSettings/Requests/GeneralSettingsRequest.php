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
     * @var string[]|null
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
     * @param string[]|null $excludedCategories
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
     * Transforms the request to a GeneralSettings object.
     *
     * @return GeneralSettings
     */
    public function transformToDomainModel(): object
    {
        return new GeneralSettings(
            $this->showSeQuraCheckoutAsHostedPage,
            $this->sendOrderReportsPeriodicallyToSeQura,
            $this->allowedIPAddresses,
            $this->excludedProducts,
            $this->excludedCategories
        );
    }
}
